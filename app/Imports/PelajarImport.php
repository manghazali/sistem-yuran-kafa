<?php

namespace App\Imports;

use App\Models\Kelas;
use App\Models\Pelajar;
use App\Models\Penjaga;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class PelajarImport implements SkipsEmptyRows, SkipsOnFailure, ToModel, WithHeadingRow, WithValidation
{
    use Importable, SkipsFailures;

    public int $bilangBerjaya = 0;

    /** @var array<int, string> */
    public array $dilangkau = [];

    public function model(array $row): ?Model
    {
        $noDaftar = trim((string) ($row['no_daftar'] ?? ''));

        if ($noDaftar === '') {
            return null;
        }

        if (Pelajar::where('no_daftar', $noDaftar)->exists()) {
            $this->dilangkau[] = "No. Daftar {$noDaftar}: dilangkau kerana sudah wujud dalam sistem.";

            return null;
        }

        $jantina = $this->uraiJantina($row['jantina'] ?? null);

        if (! $jantina) {
            $this->dilangkau[] = "No. Daftar {$noDaftar}: dilangkau kerana jantina tidak sah (guna Lelaki/Perempuan).";

            return null;
        }

        $kelasId = null;
        $namaKelas = trim((string) ($row['kelas'] ?? ''));

        if ($namaKelas !== '') {
            $kelasId = Kelas::whereRaw('LOWER(nama_kelas) = ?', [mb_strtolower($namaKelas)])->value('id');

            if (! $kelasId) {
                $this->dilangkau[] = "No. Daftar {$noDaftar}: kelas \"{$namaKelas}\" tidak dijumpai, pelajar ditambah tanpa kelas.";
            }
        }

        $penjagaId = null;
        $noKpPenjaga = trim((string) ($row['no_kp_penjaga'] ?? ''));

        if ($noKpPenjaga !== '') {
            $penjagaId = Penjaga::where('no_kp', $noKpPenjaga)->value('id');

            if (! $penjagaId) {
                $this->dilangkau[] = "No. Daftar {$noDaftar}: penjaga dengan No. KP \"{$noKpPenjaga}\" tidak dijumpai, pelajar ditambah tanpa penjaga.";
            }
        }

        $darjahMentah = trim((string) ($row['tahun'] ?? $row['darjah'] ?? ''));
        $darjah = $this->uraiDarjah($darjahMentah);

        if ($darjahMentah !== '' && ! $darjah) {
            $this->dilangkau[] = "No. Daftar {$noDaftar}: tahun \"{$darjahMentah}\" tidak sah (guna Tahun 1-6), pelajar ditambah tanpa tahun.";
        }

        $this->bilangBerjaya++;

        return new Pelajar([
            'no_daftar' => $noDaftar,
            'nama' => trim((string) ($row['nama'] ?? '')),
            'no_kp' => $this->kosongKeNull($row['no_kp'] ?? null),
            'tarikh_lahir' => $this->uraiTarikh($row['tarikh_lahir'] ?? null),
            'jantina' => $jantina,
            'kelas_id' => $kelasId,
            'darjah' => $darjah,
            'penjaga_id' => $penjagaId,
            'status' => $this->uraiStatus($row['status'] ?? null),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'no_daftar' => ['required', 'string', 'max:50'],
            'nama' => ['required', 'string', 'max:255'],
            'jantina' => ['required'],
        ];
    }

    private function kosongKeNull(mixed $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function uraiJantina(mixed $value): ?string
    {
        $value = mb_strtolower(trim((string) $value));

        return match ($value) {
            'lelaki', 'l', 'male', 'm' => 'Lelaki',
            'perempuan', 'p', 'female', 'f' => 'Perempuan',
            default => null,
        };
    }

    private function uraiDarjah(string $value): ?string
    {
        $value = mb_strtolower(trim($value));

        if ($value === '') {
            return null;
        }

        if (preg_match('/^(?:tahun\s*|darjah\s*)?([1-6])$/', $value, $padanan)) {
            return 'Tahun '.$padanan[1];
        }

        return null;
    }

    private function uraiStatus(mixed $value): string
    {
        $value = mb_strtolower(trim((string) $value));

        return match ($value) {
            'tidak aktif', 'inactive' => 'Tidak Aktif',
            'tamat', 'ended' => 'Tamat',
            default => 'Aktif',
        };
    }

    private function uraiTarikh(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return ExcelDate::excelToDateTimeObject((float) $value)->format('Y-m-d');
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        foreach (['d/m/Y', 'd-m-Y', 'Y-m-d'] as $format) {
            try {
                return Carbon::createFromFormat($format, trim((string) $value))->format('Y-m-d');
            } catch (\Throwable) {
                continue;
            }
        }

        try {
            return Carbon::parse((string) $value)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }
}
