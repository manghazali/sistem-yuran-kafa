{{--
    Dropdown boleh-cari (searchable combobox) guna Alpine — gantian untuk
    <select> biasa bila senarai pilihan datang daripada pertanyaan pangkalan
    data dan berpotensi jadi panjang (cth: Pelajar, Penjaga, Kelas, Struktur
    Yuran, Kategori Yuran, Pengguna). JANGAN guna untuk senarai pilihan tetap
    yang pendek (cth: Status Aktif/Tidak Aktif) — carian tidak membantu di
    situ.

    Props:
    - name: nama medan borang (dihantar sebagai <input type="hidden">)
    - id: id untuk kotak carian (lalai: sama dengan $name)
    - options: array ['value' => ..., 'label' => '...'] — label jugalah teks
      dicari (huruf kecil-besar diabaikan)
    - selected: nilai dipilih semasa (lalai guna old($name, $selected))
    - emptyLabel: teks pilihan "kosong" di atas sekali (cth: "- Pilih Pelajar -"
      atau "Semua Pelajar" untuk penapis) — turut boleh diklik untuk kosongkan
    - placeholder: placeholder kotak carian
    - required, disabled: sama seperti atribut HTML biasa
    - autoSubmit: true akan auto-hantar borang induk sebaik sahaja satu
      pilihan diklik (gantian untuk onchange="this.form.submit()")

    TIDAK sesuai untuk medan di dalam gelung ulang-tambah (x-for repeater,
    cth: borang Guru Kelas & Tahun) — guna <select> biasa di situ.
--}}
@props([
    'name',
    'id' => null,
    'options' => [],
    'selected' => null,
    'emptyLabel' => '- Pilih -',
    'placeholder' => 'Taip untuk cari...',
    'required' => false,
    'disabled' => false,
    'autoSubmit' => false,
])
@php
    $id ??= $name;
    $senaraiPilihan = collect($options)
        ->map(fn ($o) => ['value' => (string) $o['value'], 'label' => (string) $o['label']])
        ->prepend(['value' => '', 'label' => $emptyLabel])
        ->values();
@endphp
<div
    x-data="{
        pilihan: @js($senaraiPilihan),
        nilai: '{{ old($name, $selected) }}',
        carian: '',
        terbuka: false,
        get labelTerpilih() {
            return this.pilihan.find(o => o.value === this.nilai)?.label ?? '';
        },
        get tertapis() {
            if (! this.carian.trim() || this.carian === this.labelTerpilih) return this.pilihan;
            const q = this.carian.trim().toLowerCase();
            return this.pilihan.filter(o => o.label.toLowerCase().includes(q));
        },
        pilih(opt) {
            this.nilai = opt.value;
            this.carian = opt.label;
            this.terbuka = false;
            @if ($autoSubmit)
                this.$nextTick(() => this.$el.closest('form')?.submit());
            @endif
        },
        tutup() {
            this.terbuka = false;
            this.carian = this.labelTerpilih;
        },
        init() {
            this.carian = this.labelTerpilih;
        },
    }"
    x-on:click.outside="tutup()"
    :class="terbuka && 'z-20'"
    {{ $attributes->merge(['class' => 'relative']) }}
>
    <input
        type="text"
        id="{{ $id }}"
        x-model="carian"
        x-on:focus="terbuka = true; $event.target.select()"
        x-on:click="terbuka = true"
        x-on:keydown.escape="tutup()"
        autocomplete="off"
        placeholder="{{ $placeholder }}"
        @disabled($disabled)
        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 text-sm"
    >
    <input type="hidden" name="{{ $name }}" x-model="nilai" @disabled($disabled) @if ($required) required @endif>
    <ul x-show="terbuka" x-cloak
        class="absolute z-20 mt-1 w-full max-h-60 overflow-auto rounded-md bg-white text-sm py-1 shadow-xl ring-1 ring-gray-900/10"
    >
        <template x-for="opt in tertapis" :key="opt.value">
            <li
                x-on:click="pilih(opt)"
                x-text="opt.label"
                class="px-3 py-2 cursor-pointer hover:bg-green-50"
                :class="nilai === opt.value ? 'bg-green-50 font-medium' : ''"
            ></li>
        </template>
        <li x-show="tertapis.length === 0" class="px-3 py-2 text-gray-400">Tiada padanan.</li>
    </ul>
</div>
