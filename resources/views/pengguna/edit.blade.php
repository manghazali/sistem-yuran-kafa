<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Kemaskini Pengguna') }}</h2>
    </x-slot>

    <div class="py-4">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                @unless ($bolehTukarPeranan)
                    <div class="px-4 pt-4">
                        <p class="text-sm text-gray-500">
                            Pengguna ini memegang peranan <strong>{{ $pengguna->roles->pluck('name')->implode(', ') }}</strong>,
                            dipautkan kepada rekod Guru/Penjaga. Kemaskini nama/kelas/kelayakan sila guna menu
                            Guru atau Penjaga — skrin ini hanya untuk kemaskini maklumat asas akaun.
                        </p>
                    </div>
                @endunless

                <form method="POST" action="{{ route('pengguna.update', $pengguna) }}" class="p-4 space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <x-input-label for="name" value="Nama Penuh" />
                        <x-text-input type="text" id="name" name="name" value="{{ old('name', $pengguna->name) }}" class="mt-1 block w-full" />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="email" value="Emel" />
                        <x-text-input type="email" id="email" name="email" value="{{ old('email', $pengguna->email) }}" class="mt-1 block w-full" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="phone" value="No. Telefon" />
                        <x-text-input type="text" id="phone" name="phone" value="{{ old('phone', $pengguna->phone) }}" class="mt-1 block w-full" />
                        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="ic_number" value="No. Kad Pengenalan" />
                        <x-text-input type="text" id="ic_number" name="ic_number" value="{{ old('ic_number', $pengguna->ic_number) }}" class="mt-1 block w-full" />
                        <x-input-error :messages="$errors->get('ic_number')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="role" value="Peranan" />
                        @if ($bolehTukarPeranan)
                            <x-select id="role" name="role" class="mt-1 block w-full">
                                <option value="Admin" @selected(old('role', $pengguna->roles->first()?->name) === 'Admin')>Admin</option>
                                <option value="Akauntan" @selected(old('role', $pengguna->roles->first()?->name) === 'Akauntan')>Akauntan</option>
                            </x-select>
                        @else
                            <input type="text" value="{{ $pengguna->roles->pluck('name')->implode(', ') }}" disabled class="mt-1 block w-full rounded-md border-gray-200 bg-gray-100 text-gray-500 shadow-sm text-sm">
                        @endif
                        <x-input-error :messages="$errors->get('role')" class="mt-2" />
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="is_active" name="is_active" value="1" @checked(old('is_active', $pengguna->is_active)) class="rounded border-gray-300 text-green-600 focus:ring-green-500">
                        <label for="is_active" class="text-sm text-gray-700">Akaun Aktif (boleh log masuk)</label>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4">
                        <a href="{{ route('pengguna.index') }}" class="text-sm text-gray-600 hover:underline">Batal</a>
                        <x-primary-button>Kemaskini</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
