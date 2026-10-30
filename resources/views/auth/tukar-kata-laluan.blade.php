<x-guest-layout>
    <div class="flex justify-center mb-2">
        <img src="{{ asset('images/logo.png') }}" alt="Logo Sekolah" class="h-20 w-20 object-contain">
    </div>
    <div class="text-center mb-6">
        <h1 class="text-xl font-bold text-gray-800">Tukar Kata Laluan</h1>
        <p class="mt-2 text-sm text-gray-600">
            Anda log masuk dengan kata laluan sementara. Sila tetapkan kata laluan anda sendiri sebelum meneruskan.
        </p>
    </div>

    <form method="POST" action="{{ route('kata-laluan.tukar.simpan') }}">
        @csrf
        @method('PUT')

        <div>
            <x-input-label for="current_password" value="Kata Laluan Sementara" />
            <x-text-input id="current_password" class="block mt-1 w-full" type="password" name="current_password" required autofocus autocomplete="current-password" placeholder="Kata laluan dalam emel" />
            <x-input-error :messages="$errors->get('current_password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" value="Kata Laluan Baharu" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" placeholder="Sekurang-kurangnya 8 aksara" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" value="Sahkan Kata Laluan Baharu" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Taip semula kata laluan baharu" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="mt-6">
            <x-primary-button class="w-full justify-center bg-green-600 hover:bg-green-700 focus:ring-green-500">
                Simpan Kata Laluan
            </x-primary-button>
        </div>
    </form>

    <form method="POST" action="{{ route('logout') }}" class="mt-4 text-center">
        @csrf
        <button type="submit" class="text-sm text-gray-500 hover:text-green-700 underline">Log keluar</button>
    </form>
</x-guest-layout>
