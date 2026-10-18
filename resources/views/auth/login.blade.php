<x-guest-layout>
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="flex justify-center mb-2">
        <img src="{{ asset('images/logo.png') }}" alt="Logo Sekolah" class="h-24 w-24 object-contain">
    </div>
    <div class="text-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Sistem Pengurusan Yuran Pelajar</h1>
    </div>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-4">
            <x-input-label value="Log Masuk Sebagai" />
            <div class="mt-1 grid grid-cols-2 gap-1 p-1 bg-gray-100 rounded-lg">
                <label class="cursor-pointer">
                    <input type="radio" name="login_type" value="penjaga" class="hidden peer" {{ old('login_type', 'penjaga') === 'penjaga' ? 'checked' : '' }}>
                    <div class="peer-checked:bg-white peer-checked:text-green-700 peer-checked:shadow text-center text-sm font-medium text-gray-500 rounded-md py-2 transition">
                        Ibu Bapa / Penjaga
                    </div>
                </label>
                <label class="cursor-pointer">
                    <input type="radio" name="login_type" value="staff" class="hidden peer" {{ old('login_type') === 'staff' ? 'checked' : '' }}>
                    <div class="peer-checked:bg-white peer-checked:text-green-700 peer-checked:shadow text-center text-sm font-medium text-gray-500 rounded-md py-2 transition">
                        Kakitangan
                    </div>
                </label>
            </div>
            <x-input-error :messages="$errors->get('login_type')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="email" value="Alamat Emel" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="Masukkan alamat emel" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>
        <div class="mt-4">
            <x-input-label for="password" value="Kata Laluan" />

            <div class="relative">
                <x-text-input id="password" class="block mt-1 w-full pr-10" type="password" name="password" required autocomplete="current-password" placeholder="Masukkan kata laluan" />

                <button type="button" onclick="togglePassword()" id="togglePasswordBtn" class="absolute inset-y-0 right-0 flex items-center pr-3 mt-1 text-gray-400 hover:text-green-600">
                    <i class="fa-solid fa-eye"></i>
                </button>
            </div>

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-green-600 shadow-sm focus:ring-green-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">Ingat saya</span>
            </label>
        </div>

        <div class="flex items-center justify-between mt-4">
            @if (Route::has('password.request'))
                <a class="text-sm text-gray-600 hover:text-green-700 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500" href="{{ route('password.request') }}">
                    Lupa kata laluan?
                </a>
            @endif

            <x-primary-button class="ms-3 bg-green-600 hover:bg-green-600 focus:ring-green-500">
                Log Masuk
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
    function togglePassword() {
        const password = document.getElementById('password');
        const icon = document.querySelector('#togglePasswordBtn i');

        if (password.type === 'password') {
            password.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            password.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }
</script>
