<x-guest-layout>
    <div class="flex justify-center mb-2">
        <img src="{{ asset('images/logo.png') }}" alt="Logo Sekolah" class="h-24 w-24 object-contain">
    </div>
    <div class="text-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Tetapkan Semula Kata Laluan</h1>
    </div>

    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <x-input-label for="email" value="Alamat Emel" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" placeholder="Masukkan alamat emel" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" value="Kata Laluan Baharu" />

            <div class="relative">
                <x-text-input id="password" class="block mt-1 w-full pr-10" type="password" name="password" required autocomplete="new-password" placeholder="Masukkan kata laluan baharu" />

                <button type="button" onclick="togglePassword('password', 'togglePasswordBtn')" id="togglePasswordBtn" class="absolute inset-y-0 right-0 flex items-center pr-3 mt-1 text-gray-400 hover:text-green-600">
                    <i class="fa-solid fa-eye"></i>
                </button>
            </div>

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" value="Sahkan Kata Laluan Baharu" />

            <div class="relative">
                <x-text-input id="password_confirmation" class="block mt-1 w-full pr-10" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Masukkan semula kata laluan baharu" />

                <button type="button" onclick="togglePassword('password_confirmation', 'togglePasswordConfirmBtn')" id="togglePasswordConfirmBtn" class="absolute inset-y-0 right-0 flex items-center pr-3 mt-1 text-gray-400 hover:text-green-600">
                    <i class="fa-solid fa-eye"></i>
                </button>
            </div>

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-6">
            <x-primary-button class="ms-3 bg-green-600 hover:bg-green-600 focus:ring-green-500">
                Tetapkan Semula Kata Laluan
            </x-primary-button>
        </div>
    </form>
    <div class="mt-8 pt-6 border-t border-gray-100 text-center">
        <p class="text-xs text-gray-400">
            © {{ date('Y') }} Sistem Pengurusan Yuran Pelajar
        </p>
    </div>
</x-guest-layout>
<script>
    function togglePassword(inputId, buttonId) {
        const input = document.getElementById(inputId);
        const icon = document.querySelector('#' + buttonId + ' i');

        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }
</script>
