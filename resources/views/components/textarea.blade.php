{{--
    <textarea> biasa dengan gaya DISERAGAMKAN (sama dengan x-text-input).
--}}
@props(['disabled' => false])

<textarea @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-300 focus:border-green-500 focus:ring-green-500 rounded-md shadow-sm text-sm']) }}>{{ $slot }}</textarea>
