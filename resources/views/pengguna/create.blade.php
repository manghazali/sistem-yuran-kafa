<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Tambah Pengguna') }}</h2>
    </x-slot>

    <div class="py-4">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                <div class="px-4 pt-4">
                    <p class="text-sm text-gray-500">
                        Untuk akaun <strong>Guru</strong> atau <strong>Penjaga</strong>, sila cipta melalui menu
                        <a href="{{ route('guru.index') }}" class="text-green-600 hover:underline">Guru</a> atau
                        <a href="{{ route('penjaga.index') }}" class="text-green-600 hover:underline">Penjaga</a>
                        supaya akaun terus dipautkan dengan rekod berkaitan. Skrin ini khusus untuk akaun
                        <strong>Admin</strong>/<strong>Akauntan</strong> yang berdiri sendiri.
                    </p>
                </div>
                <form method="POST" action="{{ route('pengguna.store') }}" class="p-4 space-y-4">
                    @csrf
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Nama Penuh</label>
                        <x-text-input type="text" id="name" name="name" value="{{ old('name') }}" class="mt-1 block w-full" />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Emel</label>
                        <x-text-input type="email" id="email" name="email" value="{{ old('email') }}" class="mt-1 block w-full" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700">No. Telefon</label>
                        <x-text-input type="text" id="phone" name="phone" value="{{ old('phone') }}" class="mt-1 block w-full" />
                        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                    </div>
                    <div>
                        <label for="ic_number" class="block text-sm font-medium text-gray-700">No. Kad Pengenalan</label>
                        <x-text-input type="text" id="ic_number" name="ic_number" value="{{ old('ic_number') }}" class="mt-1 block w-full" />
                        <x-input-error :messages="$errors->get('ic_number')" class="mt-2" />
                    </div>
                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-700">Peranan</label>
                        <x-select id="role" name="role" class="mt-1 block w-full">
                            <option value="Admin" @selected(old('role') === 'Admin')>Admin</option>
                            <option value="Akauntan" @selected(old('role') === 'Akauntan')>Akauntan</option>
                        </x-select>
                        <x-input-error :messages="$errors->get('role')" class="mt-2" />
                    </div>
                    <p class="text-xs text-gray-500">Kata laluan sementara akan dijana secara rawak dan dihantar ke emel di atas.</p>

                    <div class="flex items-center justify-end gap-3 pt-4">
                        <a href="{{ route('pengguna.index') }}" class="text-sm text-gray-600">Batal</a>
                        <x-primary-button>Cipta Pengguna</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
