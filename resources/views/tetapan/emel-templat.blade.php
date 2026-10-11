<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Templat Emel') }}</h2>
    </x-slot>
    <div class="py-4 space-y-6">
            @if (session('status'))
                <div class="rounded-md bg-green-50 border border-green-200 px-4 py-2 text-sm text-green-700">{{ session('status') }}</div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden" x-data="{ tab: '{{ old('_tab', $templat->keys()->first()) }}' }">
                <div class="flex border-b border-gray-200">
                    @foreach ($templat as $kunci => $data)
                        <button type="button" @click="tab = '{{ $kunci }}'"
                            :class="tab === '{{ $kunci }}' ? 'border-green-600 text-green-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="px-4 py-3 text-sm font-medium border-b-2 -mb-px transition">
                            {{ $data['label'] }}
                        </button>
                    @endforeach
                </div>

                @foreach ($templat as $kunci => $data)
                    <div x-show="tab === '{{ $kunci }}'" x-cloak>
                        <div class="px-4 pt-4">
                            <p class="text-sm text-gray-500">{{ $data['keterangan'] }}</p>
                        </div>
                        <form method="POST" action="{{ route('tetapan.emel-templat.update', $kunci) }}">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="_tab" value="{{ $kunci }}">
                            <div class="p-4 space-y-4">
                                <div>
                                    <label for="subjek-{{ $kunci }}" class="block text-sm font-medium text-gray-700">Subjek Emel</label>
                                    <x-text-input type="text" id="subjek-{{ $kunci }}" name="subjek" value="{{ old('subjek', $data['subjek']) }}" class="mt-1 block w-full" />
                                </div>
                                <div>
                                    <label for="mesej-{{ $kunci }}" class="block text-sm font-medium text-gray-700">Mesej</label>
                                    <x-textarea id="mesej-{{ $kunci }}" name="mesej" rows="8" class="mt-1 block w-full font-mono">{{ old('mesej', $data['mesej']) }}</x-textarea>
                                    <p class="mt-1 text-xs text-gray-500">
                                        Placeholder tersedia:
                                        @foreach ($data['placeholder'] as $ph)
                                            <code class="px-1 py-0.5 bg-gray-100 rounded">{{ $ph }}</code>
                                        @endforeach
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center justify-end border-t border-gray-200 bg-gray-50 px-4 py-3">
                                <button type="submit" class="inline-flex items-center gap-2 rounded-md bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                                    Simpan
                                </button>
                            </div>
                        </form>
                    </div>
                @endforeach
            </div>
    </div>
</x-app-layout>
