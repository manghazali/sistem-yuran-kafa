<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Tetapan Gerbang Pembayaran') }}</h2>
    </x-slot>

    <div class="py-4">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 p-4 bg-green-100 text-green-800 rounded-md">{{ session('status') }}</div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <p class="text-sm text-gray-500 mb-6">
                    Kelayakan ini disimpan dalam pangkalan data (disulitkan/encrypted),
                    bukan dalam fail <code>.env</code> — anda boleh tukar terus dari sini
                    tanpa akses server.
                </p>

                <form method="POST" action="{{ route('tetapan.payment-gateway.update') }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="merchant_id" value="Merchant ID" />
                        <x-text-input id="merchant_id" name="merchant_id" type="text" class="mt-1 block w-full font-mono" value="{{ old('merchant_id', $merchantId) }}" required />
                        <x-input-error :messages="$errors->get('merchant_id')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="secret_key" value="Secret Key" />
                        <x-text-input id="secret_key" name="secret_key" type="password" class="mt-1 block w-full font-mono" value="" autocomplete="new-password" placeholder="{{ $secretKeySudahDitetapkan ? '•••••••••••••••• (sudah ditetapkan — isi hanya jika nak tukar)' : 'Belum ditetapkan' }}" />
                        <p class="text-xs text-gray-500 mt-1">
                            Demi keselamatan, nilai sedia ada tidak dipaparkan. Biar kosong untuk
                            KEKALKAN Secret Key semasa — isi hanya jika anda nak menukarnya.
                        </p>
                        <x-input-error :messages="$errors->get('secret_key')" class="mt-2" />
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="sandbox" name="sandbox" value="1" class="rounded border-gray-300" @checked($sandbox == '1' || $sandbox === true)>
                        <label for="sandbox" class="text-sm text-gray-700">
                            Mod Sandbox (ujian — tiada wang sebenar). Nyahtanda bila dah sedia untuk produksi sebenar.
                        </label>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4">
                        <x-primary-button>Simpan Tetapan</x-primary-button>
                    </div>
                </form>
            </div>

            {{-- URL notifikasi (webhook) untuk ditampal di papan pemuka DOKU jika diperlukan. --}}
            <div class="mt-4 bg-white overflow-hidden shadow-sm sm:rounded-lg p-6" x-data="{ disalin: false }">
                <h3 class="font-semibold text-gray-800">URL Notifikasi</h3>
                <p class="text-sm text-gray-500 mt-1">
                    Sistem menghantar URL ini kepada DOKU bagi setiap bayaran. Jika papan pemuka DOKU
                    meminta Notification URL, gunakan URL ini. Notifikasi tanpa token ini akan ditolak.
                </p>
                <div class="mt-3 flex gap-2">
                    <x-text-input id="url_notifikasi" type="text" class="block w-full font-mono text-xs" value="{{ $urlNotifikasi }}" readonly />
                    <button type="button" class="inline-flex items-center gap-1 rounded-md border border-gray-300 px-3 text-sm text-gray-700 hover:bg-gray-50"
                            @click="navigator.clipboard.writeText(document.getElementById('url_notifikasi').value); disalin = true; setTimeout(() => disalin = false, 2000)">
                        <i class="fas fa-copy"></i> <span x-text="disalin ? 'Disalin' : 'Salin'"></span>
                    </button>
                </div>
            </div>

            <div class="mt-4 p-4 bg-yellow-50 text-yellow-800 text-sm rounded-md">
                Jangan kongsi Secret Key dengan sesiapa. Sesiapa yang ada Secret Key
                boleh jana permintaan bayaran atas nama sistem anda.
            </div>
        </div>
    </div>
</x-app-layout>
