<?php

namespace App\Services;

use App\Models\Bayaran;
use App\Models\BayaranItem;
use App\Models\Pelajar;
use App\Models\TetapanYuranPelajar;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class BakiBulananService
{
    /**
     * Pra-ambil data (tetapan yuran aktif, bayaran tepat, kolam bayaran tanpa
     * agihan) untuk SEBILANGAN pelajar sekali gus — mengelakkan N+1 query
     * apabila mengira baki ramai pelajar (cth: papan pemuka Admin). Struktur
     * pulangan sepadan dengan apa yang diperlukan oleh senaraiBulan() bagi
     * setiap pelajar individu.
     *
     * @param  Collection<int, int>  $pelajarId
     * @return array{tetapan: Collection, dibayarTepat: Collection, kolam: Collection}
     */
    public function prafetch(Collection $pelajarId, int $tahun): array
    {
        $tetapan = TetapanYuranPelajar::with('strukturYuran')
            ->whereIn('pelajar_id', $pelajarId)
            ->where('status', 'Aktif')
            ->orderBy('id')
            ->get()
            ->groupBy('pelajar_id');

        $dibayarTepat = BayaranItem::query()
            ->join('bayaran', 'bayaran.id', '=', 'bayaran_item.bayaran_id')
            ->whereIn('bayaran.pelajar_id', $pelajarId)
            ->where('bayaran.tahun', $tahun)
            ->selectRaw('bayaran.pelajar_id, bayaran_item.tetapan_yuran_pelajar_id, bayaran_item.bulan, SUM(bayaran_item.amaun) as jumlah')
            ->groupBy('bayaran.pelajar_id', 'bayaran_item.tetapan_yuran_pelajar_id', 'bayaran_item.bulan')
            ->get()
            ->groupBy('pelajar_id')
            ->map(fn ($baris) => $baris->groupBy('bulan')->map(fn ($b) => $b->pluck('jumlah', 'tetapan_yuran_pelajar_id')));

        $kolam = Bayaran::whereIn('pelajar_id', $pelajarId)
            ->where('tahun', $tahun)
            ->withCount('items')
            ->get()
            ->filter(fn ($b) => $b->items_count === 0)
            ->groupBy('pelajar_id')
            ->map(fn ($baris) => $baris->groupBy('bulan')->map(fn ($b) => (float) $b->sum('amaun')));

        return ['tetapan' => $tetapan, 'dibayarTepat' => $dibayarTepat, 'kolam' => $kolam];
    }

    /**
     * @param  array{tetapan: Collection, dibayarTepat: Collection, kolam: Collection}|null  $prafetch
     *                                                                                                  Jika diberi (cth: hasil prafetch() dipanggil sekali untuk ramai pelajar), guna data itu
     *                                                                                                  terus tanpa query tambahan. Jika tiada, query seperti biasa untuk pelajar ini sahaja.
     */
    public function senaraiBulan(Pelajar $pelajar, int $tahun, ?array $prafetch = null, bool $sejakDitetapkan = false): Collection
    {
        if ($prafetch) {
            $tetapanAktif = $prafetch['tetapan']->get($pelajar->id, collect());
            $dibayarTepatMengikutBulan = $prafetch['dibayarTepat']->get($pelajar->id, collect());
            $kolamTanpaAgihanMengikutBulan = $prafetch['kolam']->get($pelajar->id, collect());
        } else {
            $tetapanAktif = TetapanYuranPelajar::with('strukturYuran')
                ->where('pelajar_id', $pelajar->id)
                ->where('status', 'Aktif')
                ->orderBy('id')
                ->get();

            // Bayaran yang tepat direkod terhadap item tertentu (cth: bayaran melalui portal —
            // pilihan checkbox per-item). Dikumpul mengikut [bulan][tetapan_yuran_pelajar_id].
            $dibayarTepatMengikutBulan = BayaranItem::query()
                ->join('bayaran', 'bayaran.id', '=', 'bayaran_item.bayaran_id')
                ->where('bayaran.pelajar_id', $pelajar->id)
                ->where('bayaran.tahun', $tahun)
                ->selectRaw('bayaran_item.tetapan_yuran_pelajar_id, bayaran_item.bulan, SUM(bayaran_item.amaun) as jumlah')
                ->groupBy('bayaran_item.tetapan_yuran_pelajar_id', 'bayaran_item.bulan')
                ->get()
                ->groupBy('bulan')
                ->map(fn ($baris) => $baris->pluck('jumlah', 'tetapan_yuran_pelajar_id'));

            // Bayaran lama/manual yang tidak dipautkan kepada item tertentu (cth: dicatat oleh
            // admin sebagai jumlah pukal sebulan). Diagihkan secara automatik kepada item yang
            // masih terhutang dalam bulan berkenaan, mengikut turutan id.
            $kolamTanpaAgihanMengikutBulan = Bayaran::where('pelajar_id', $pelajar->id)
                ->where('tahun', $tahun)
                ->withCount('items')
                ->get()
                ->filter(fn ($b) => $b->items_count === 0)
                ->groupBy('bulan')
                ->map(fn ($baris) => (float) $baris->sum('amaun'));
        }

        $senarai = collect();

        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $awalBulan = Carbon::create($tahun, $bulan, 1)->startOfDay();
            $akhirBulan = $awalBulan->copy()->endOfMonth();

            // Bulan yang dikenakan kini ditentukan SEPENUHNYA oleh Struktur
            // Yuran (bulan_berkenaan) — tiada lagi tarikh pada tetapan pelajar
            // itu sendiri:
            // Ada bulan_berkenaan (Kategori Yuran struktur ini "Yuran Bulanan"):
            //   terpakai pada bulan² yang disenaraikan di situ, SETIAP TAHUN
            //   berulang.
            // Tiada bulan_berkenaan (kategori lain): dikenakan SEKALI sahaja,
            //   pada bulan created_at tetapan ini (bulan ia DICIPTA — cth:
            //   yuran pendaftaran dikenakan pada bulan pelajar didaftarkan).
            $tetapanTerpakai = $tetapanAktif->filter(function ($t) use ($bulan, $awalBulan, $akhirBulan, $sejakDitetapkan) {
                $bulanBerkenaan = $t->strukturYuran->bulan_berkenaan;

                if ($bulanBerkenaan !== null) {
                    // $sejakDitetapkan: abaikan bulan sebelum yuran ini ditetapkan
                    // kepada pelajar (digunakan untuk tunggakan tahun lepas, supaya
                    // pelajar baharu tidak dikira berhutang bulan sebelum mereka masuk).
                    if ($sejakDitetapkan && $akhirBulan->lt($t->created_at)) {
                        return false;
                    }

                    // Perbandingan LONGGAR (bukan strict) — elak isu jenis data
                    // (cth: nilai tersimpan sebagai string "1" bukan int 1).
                    return in_array($bulan, $bulanBerkenaan);
                }

                return $t->created_at->between($awalBulan, $akhirBulan);
            });

            if ($tetapanTerpakai->isEmpty()) {
                continue;
            }

            $dibayarTepatBulanIni = $dibayarTepatMengikutBulan->get($bulan, collect());
            $kolamBaki = $kolamTanpaAgihanMengikutBulan->get($bulan, 0.0);

            $butiran = $tetapanTerpakai->map(function ($t) use ($dibayarTepatBulanIni, &$kolamBaki, $awalBulan) {
                $amaunItem = (float) $t->strukturYuran->amaun;
                $dibayarTepat = (float) ($dibayarTepatBulanIni[$t->id] ?? 0);
                $bakiSelepasTepat = max(0, round($amaunItem - $dibayarTepat, 2));

                $ambilDariKolam = 0.0;
                if ($bakiSelepasTepat > 0 && $kolamBaki > 0) {
                    $ambilDariKolam = min($kolamBaki, $bakiSelepasTepat);
                    $kolamBaki = round($kolamBaki - $ambilDariKolam, 2);
                }

                $dibayarItem = round($dibayarTepat + $ambilDariKolam, 2);
                $bakiItem = max(0, round($amaunItem - $dibayarItem, 2));

                // Struktur dengan bulan_berkenaan (berulang) dipaparkan sebagai
                // bulan/tahun SEBENAR ia dikenakan (cth: "Oktober 2026") — bukan
                // nama statik Struktur Yuran, supaya label sentiasa betul
                // mengikut bulan dikenakan (Januari bulan Januari, Februari
                // bulan Februari, dst), bukan "Januari" sepanjang tahun hanya
                // kerana itu nama asal struktur.
                $berulang = $t->strukturYuran->bulan_berkenaan !== null;
                $nama = $berulang
                    ? $awalBulan->translatedFormat('F Y')
                    : $t->strukturYuran->nama;

                return [
                    'id' => $t->id,
                    'struktur_yuran_id' => $t->struktur_yuran_id,
                    'nama' => $nama,
                    'berulang' => $berulang,
                    'amaun' => $amaunItem,
                    'dibayar' => $dibayarItem,
                    'baki' => $bakiItem,
                    'status' => match (true) {
                        $bakiItem <= 0 => 'Dibayar',
                        $dibayarItem > 0 => 'Sebahagian',
                        default => 'Belum Bayar',
                    },
                ];
            })->values();

            $jumlah = $butiran->sum('amaun');
            $dibayar = $butiran->sum('dibayar');
            $baki = round($jumlah - $dibayar, 2);

            $status = match (true) {
                $baki <= 0 => 'Dibayar',
                $dibayar > 0 => 'Sebahagian',
                default => 'Belum Bayar',
            };

            $senarai->push([
                'bulan' => $bulan,
                'tahun' => $tahun,
                'label' => $awalBulan->translatedFormat('F Y'),
                'jumlah' => $jumlah,
                'dibayar' => $dibayar,
                'baki' => max(0, $baki),
                'status' => $status,
                'boleh_pilih' => $baki > 0,
                'butiran' => $butiran,
            ]);
        }

        return $senarai;
    }

    /**
     * Tahun yang yuran belum bayarnya dipaparkan dan boleh dibayar oleh penjaga:
     * tunggakan tahun lepas dan tahun semasa.
     *
     * @return array<int, int>
     */
    public function tahunBolehBayar(): array
    {
        return [now()->year - 1, now()->year];
    }

    /**
     * @return array<int, array> prafetch() bagi setiap tahun boleh bayar
     */
    public function prafetchTahunBolehBayar(Collection $pelajarId): array
    {
        return collect($this->tahunBolehBayar())->mapWithKeys(fn ($tahun) => [$tahun => $this->prafetch($pelajarId, $tahun)])->all();
    }

    /**
     * Bulan yang masih ada baki bagi tahun-tahun boleh bayar (tahun lepas dahulu).
     * Tahun lepas hanya mengira bulan sejak yuran ditetapkan kepada pelajar.
     *
     * @param  array<int, array>|null  $prafetchMengikutTahun  hasil prafetchTahunBolehBayar()
     */
    public function bulanBelumBayar(Pelajar $pelajar, ?array $prafetchMengikutTahun = null): Collection
    {
        return collect($this->tahunBolehBayar())
            ->flatMap(fn ($tahun) => $this->senaraiBulan($pelajar, $tahun, $prafetchMengikutTahun[$tahun] ?? null, $tahun < now()->year))
            ->filter(fn ($b) => $b['baki'] > 0)
            ->values();
    }

    /**
     * @param  bool  $termasukBulanHadapan  false (lalai): hanya kira bulan yang telah
     *                                      berlalu/semasa — iaitu baki yang benar-benar TERTUNGGAK.
     *                                      true: kira SEMUA bulan dalam tahun (termasuk yang belum
     *                                      tiba) — iaitu jumlah keseluruhan BELUM DIBAYAR.
     */
    public function jumlahBakiTahunSemasa(Pelajar $pelajar, ?array $prafetch = null, bool $termasukBulanHadapan = false): float
    {
        $bulanSemasa = now()->month;

        return $this->senaraiBulan($pelajar, now()->year, $prafetch)
            ->filter(fn ($b) => ($termasukBulanHadapan || $b['bulan'] <= $bulanSemasa) && $b['baki'] > 0)
            ->sum('baki');
    }
}
