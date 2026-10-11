{{--
    <select> biasa dengan gaya DISERAGAMKAN (sama dengan x-text-input: sempadan,
    bulat sudut, bayang, fokus hijau, saiz teks text-sm). Guna untuk senarai
    pilihan TETAP yang pendek (cth: Status, Jantina, Peranan). Untuk senarai
    panjang daripada pangkalan data (Pelajar, Kelas, Penjaga, dll.), guna
    <x-searchable-select> sebaliknya.
--}}
@props(['disabled' => false])

<select @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-300 focus:border-green-500 focus:ring-green-500 rounded-md shadow-sm text-sm']) }}>
    {{ $slot }}
</select>
