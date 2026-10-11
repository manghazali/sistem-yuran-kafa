{{--
    Komponen kongsi: Kategori Yuran + Nama Struktur Yuran + (jika Kategori
    Yuran ditandakan "Bulanan") pilihan SATU bulan untuk tentukan bulan MANA
    struktur yuran ini terpakai. Guna dalam borang Tambah dan Kemaskini
    Struktur Yuran.

    Bila Kategori ditandakan Bulanan: medan "Nama Struktur Yuran" sendiri
    bertukar jadi dropdown memilih SATU bulan (Jan–Dis) — tiada medan
    "Bulan Berkenaan" berasingan lagi. Nama DIJANA AUTOMATIK daripada bulan
    dipilih (cth: "Januari") dan dihantar sebagai medan `nama` tersembunyi
    (dikuatkuasakan SEKALI LAGI di pelayan, bukan sekadar di client — lihat
    StrukturYuranController). Jika Kategori TIDAK Bulanan, medan Nama kekal
    input teks bebas seperti biasa dan tiada bulan_berkenaan dihantar.

    Diperlukan daripada caller:
    - $senaraiKategori: senarai Kategori Yuran (untuk dropdown + flag bulanan setiap satu)
    - $kategoriIdSemasa: id Kategori Yuran sedia ada (atau '' bagi borang baharu)
    - $namaSemasa: nama struktur sedia ada (atau '' bagi borang baharu)
    - $bulanBerkenaanSemasa: array nombor bulan sedia ada (atau []) — guna elemen pertama sahaja
--}}
<div x-data="{
        kategoriId: '{{ old('kategori_yuran_id', $kategoriIdSemasa) }}',
        kategoriBulananMap: {
            @foreach ($senaraiKategori as $kategori)
                '{{ $kategori->id }}': @js((bool) $kategori->bulanan),
            @endforeach
        },
        get bulananKategori() {
            return this.kategoriBulananMap[this.kategoriId] ?? false;
        },
        bulanDipilih: '{{ old('bulan_berkenaan', $bulanBerkenaanSemasa[0] ?? '') }}',
        namaBulanPenuh: @js(\App\Models\StrukturYuran::NAMA_BULAN),
        get namaAuto() {
            return this.namaBulanPenuh[Number(this.bulanDipilih)] ?? '';
        },
        nama: '{{ old('nama', $namaSemasa) }}',
    }"
    x-init="
        $watch('bulanDipilih', () => { if (bulananKategori) nama = namaAuto; });
        $watch('kategoriId', () => {
            if (bulananKategori) {
                nama = namaAuto;
            } else if (Object.values(namaBulanPenuh).includes(nama)) {
                // Kosongkan semula nama auto-jana bulan (cth: 'Mei') bila tukar
                // ke kategori bukan-bulanan — elak nama bulan tertinggal sebagai
                // teks bebas yang mengelirukan. Nama ditaip sendiri oleh admin
                // (bukan salah satu nama bulan) tidak disentuh.
                nama = '';
            }
        });
    "
>
    <div>
        <x-input-label for="kategori_yuran_id" value="Kategori Yuran" />
        <x-select id="kategori_yuran_id" name="kategori_yuran_id" x-model="kategoriId" class="mt-1 block w-full" required>
            <option value="">- Pilih Kategori -</option>
            @foreach ($senaraiKategori as $kategori)
                <option value="{{ $kategori->id }}">{{ $kategori->nama }}{{ $kategori->bulanan ? ' (Bulanan)' : '' }}</option>
            @endforeach
        </x-select>
        <x-input-error :messages="$errors->get('kategori_yuran_id')" class="mt-2" />
        <p class="text-xs text-gray-500 mt-1">
            Tiada kategori sesuai? <a href="{{ route('kategori-yuran.create') }}" class="text-blue-600 hover:underline" target="_blank">Tambah kategori baharu</a> dahulu.
        </p>
    </div>

    <div class="mt-4">
        <x-input-label for="nama" value="Nama Struktur Yuran" />

        <template x-if="bulananKategori">
            <div>
                <x-select id="bulan_berkenaan" name="bulan_berkenaan" x-model="bulanDipilih" class="mt-1 block w-full" required>
                    <option value="">- Pilih Bulan -</option>
                    @foreach (\App\Models\StrukturYuran::NAMA_BULAN as $noBulan => $namaBulan)
                        <option value="{{ $noBulan }}">{{ $namaBulan }}</option>
                    @endforeach
                </x-select>
                <input type="hidden" name="nama" x-model="nama">
                <p class="text-xs text-gray-500 mt-1">Pilih SATU bulan — struktur yuran ini berulang setiap tahun pada bulan tersebut, dan jadi Nama Struktur Yuran.</p>
                <x-input-error :messages="$errors->get('bulan_berkenaan')" class="mt-2" />
            </div>
        </template>

        <template x-if="! bulananKategori">
            <input id="nama" name="nama" type="text" x-model="nama" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500" placeholder="cth: Yuran Pendaftaran" required>
        </template>

        <x-input-error :messages="$errors->get('nama')" class="mt-2" />
    </div>
</div>
