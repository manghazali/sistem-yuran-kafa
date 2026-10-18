<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Import Guru Melalui Excel') }}</h2>
    </x-slot>

    <div class="py-2">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-4">

            @if (session('importRingkasan'))
                @php $ringkasan = session('importRingkasan'); @endphp
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 space-y-3">
                    <h3 class="font-semibold text-gray-800">{{ __('Keputusan Import') }}</h3>
                    <p class="text-sm text-green-700">{{ $ringkasan['berjaya'] }} {{ __('guru berjaya diimport.') }}</p>
                    @if ($ringkasan['akaunDicipta'])
                        <p class="text-sm text-blue-700">{{ $ringkasan['akaunDicipta'] }} {{ __('akaun log masuk dicipta & emel dihantar automatik.') }}</p>
                    @endif

                    @if (count($ringkasan['dilangkau']))
                        <div>
                            <p class="text-sm font-medium text-amber-700">{{ __('Baris dilangkau / amaran') }} ({{ count($ringkasan['dilangkau']) }})</p>
                            <ul class="mt-1 text-xs text-gray-600 list-disc list-inside space-y-0.5">
                                @foreach ($ringkasan['dilangkau'] as $mesej)
                                    <li>{{ $mesej }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if (count($ringkasan['gagal']))
                        <div>
                            <p class="text-sm font-medium text-red-700">{{ __('Baris gagal (data tidak lengkap)') }} ({{ count($ringkasan['gagal']) }})</p>
                            <ul class="mt-1 text-xs text-gray-600 list-disc list-inside space-y-0.5">
                                @foreach ($ringkasan['gagal'] as $mesej)
                                    <li>{{ $mesej }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 space-y-4">
                <div class="text-sm text-gray-600 space-y-2">
                    <p>{{ __('Muat naik fail Excel (.xlsx/.xls) atau CSV yang mengandungi senarai guru. Baris pertama mestilah nama lajur.') }}</p>
                    <p>{{ __('Lajur yang diperlukan:') }} <strong>Nama</strong>. {{ __('Lajur pilihan:') }} <strong>No Pekerja, No KP, No Telefon, Emel, Status</strong>.</p>
                    <p>{{ __('Jika lajur Emel diisi, akaun log masuk (peranan Guru) akan dicipta serta-merta dan emel kata laluan sementara akan dihantar automatik.') }}</p>
                    <p>{{ __('No. Pekerja yang sudah wujud dalam sistem akan dilangkau secara automatik.') }}</p>
                    <a href="{{ route('guru.import.template') }}" class="inline-flex items-center gap-1 text-sm text-blue-600 hover:underline">
                        <i class="fas fa-download"></i> {{ __('Muat turun templat Excel') }}
                    </a>
                </div>

                <form method="POST" action="{{ route('guru.import.store') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf

                    <div>
                        <x-input-label for="fail" value="Fail Excel / CSV" />
                        <input id="fail" name="fail" type="file" accept=".xlsx,.xls,.csv" required
                               class="mt-1 block w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-green-50 file:text-green-700 hover:file:bg-green-100" />
                        <x-input-error :messages="$errors->get('fail')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <a href="{{ route('guru.index') }}" class="text-sm text-gray-600">Batal</a>
                        <x-primary-button>Import</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
