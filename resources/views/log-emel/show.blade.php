<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Butiran Emel') }}</h2>
    </x-slot>

    <div class="py-2">
        <div class="max-w-3xl mx-auto sm:px-2 lg:px-2 space-y-4">

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 space-y-2 text-sm">
                <p><span class="text-gray-500">Kepada:</span> <strong>{{ $logEmel->kepada }}</strong>
                    @if ($logEmel->nama_penerima)
                        ({{ $logEmel->nama_penerima }})
                    @endif
                </p>
                <p><span class="text-gray-500">Subjek:</span> <strong>{{ $logEmel->subjek }}</strong></p>
                <p><span class="text-gray-500">Tarikh Hantar:</span> {{ $logEmel->created_at->format('d/m/Y H:i:s') }}</p>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                @if ($logEmel->kandungan_html)
                    <iframe srcdoc="{{ $logEmel->kandungan_html }}" class="w-full" style="min-height: 500px; border: 0;" sandbox=""></iframe>
                @else
                    <pre class="p-6 text-sm text-gray-700 whitespace-pre-wrap">{{ $logEmel->kandungan_teks }}</pre>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
