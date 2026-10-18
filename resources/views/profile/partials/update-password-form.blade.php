<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            Kemaskini Kata Laluan
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            Pastikan akaun anda menggunakan kata laluan yang panjang dan rawak untuk kekal selamat.
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        <div>
            <x-input-label for="update_password_current_password" value="Kata Laluan Semasa" />
            <div class="relative">
                <x-text-input id="update_password_current_password" name="current_password" type="password" class="mt-1 block w-full pr-10" autocomplete="current-password" />
                <button type="button" onclick="togglePassword('update_password_current_password', 'toggleCurrentPasswordBtn')" id="toggleCurrentPasswordBtn" class="absolute inset-y-0 right-0 flex items-center pr-3 mt-1 text-gray-400 hover:text-green-600">
                    <i class="fa-solid fa-eye"></i>
                </button>
            </div>
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password" value="Kata Laluan Baharu" />
            <div class="relative">
                <x-text-input id="update_password_password" name="password" type="password" class="mt-1 block w-full pr-10" autocomplete="new-password" />
                <button type="button" onclick="togglePassword('update_password_password', 'toggleNewPasswordBtn')" id="toggleNewPasswordBtn" class="absolute inset-y-0 right-0 flex items-center pr-3 mt-1 text-gray-400 hover:text-green-600">
                    <i class="fa-solid fa-eye"></i>
                </button>
            </div>
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password_confirmation" value="Sahkan Kata Laluan Baharu" />
            <div class="relative">
                <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full pr-10" autocomplete="new-password" />
                <button type="button" onclick="togglePassword('update_password_password_confirmation', 'toggleConfirmPasswordBtn')" id="toggleConfirmPasswordBtn" class="absolute inset-y-0 right-0 flex items-center pr-3 mt-1 text-gray-400 hover:text-green-600">
                    <i class="fa-solid fa-eye"></i>
                </button>
            </div>
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>Simpan</x-primary-button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >Disimpan.</p>
            @endif
        </div>
    </form>
</section>

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
