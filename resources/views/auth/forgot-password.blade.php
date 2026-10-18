<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="flex justify-center mb-2">
        <img src="{{ asset('images/logo.png') }}" alt="Logo Sekolah" class="h-24 w-24 object-contain">
    </div>
    <div class="text-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Lupa Kata Laluan</h1>
        <p class="mt-2 text-sm text-gray-500">Masukkan alamat emel anda. Kami akan hantar pautan untuk tetapkan semula kata laluan.</p>
    </div>

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div>
            <x-input-label for="email" value="Alamat Emel" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus placeholder="Masukkan alamat emel" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between mt-6">
            <a class="text-sm text-gray-600 hover:text-green-700 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500" href="{{ route('login') }}">
                &larr; Kembali ke Log Masuk
            </a>

            <x-primary-button class="ms-3 bg-green-600 hover:bg-green-600 focus:ring-green-500">
                Hantar Pautan Tetapan Semula
            </x-primary-button>
        </div>
    </form>
    <div class="mt-8 pt-6 border-t border-gray-100 text-center">
        <p class="text-xs text-gray-400">
            © {{ date('Y') }} Sistem Pengurusan Yuran Pelajar
        </p>
    </div>
</x-guest-layout>
