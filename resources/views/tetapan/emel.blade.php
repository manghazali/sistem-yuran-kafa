<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Tetapan Emel') }}</h2>
    </x-slot>
    <div class="py-4">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="rounded-md bg-green-50 border border-green-200 px-4 py-2 text-sm text-green-700">{{ session('status') }}</div>
            @endif
            @error('emel')
                <div class="rounded-md bg-red-50 border border-red-200 px-4 py-2 text-sm text-red-700">{{ $message }}</div>
            @enderror

            <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden" x-data="{ driver: '{{ old('driver', $driver) }}' }">
                <div class="p-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Tetapan Emel</h3>
                    <p class="mt-1 text-sm text-gray-500">Konfigurasi penghantaran emel sistem (reset kata laluan, akaun baharu, notifikasi).</p>
                </div>
                <form method="POST" action="{{ route('tetapan.emel.update') }}">
                    @csrf
                    @method('PUT')
                    <div class="p-4 space-y-4">
                        <div>
                            <label for="driver" class="block text-sm font-medium text-gray-700">Kaedah Penghantaran</label>
                            <x-select id="driver" name="driver" x-model="driver" class="mt-1 block w-full">
                                <option value="log" {{ old('driver', $driver) === 'log' ? 'selected' : '' }}>Log sahaja (ujian — tiada emel sebenar dihantar)</option>
                                <option value="smtp" {{ old('driver', $driver) === 'smtp' ? 'selected' : '' }}>SMTP (hantar emel sebenar)</option>
                            </x-select>
                            @error('driver')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div x-show="driver === 'smtp'" x-cloak class="space-y-4 border-t border-gray-100 pt-4">
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div class="sm:col-span-2">
                                    <label for="host" class="block text-sm font-medium text-gray-700">SMTP Host</label>
                                    <x-text-input type="text" id="host" name="host" value="{{ old('host', $host) }}" placeholder="smtp.gmail.com" class="mt-1 block w-full" />
                                    @error('host')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label for="port" class="block text-sm font-medium text-gray-700">Port</label>
                                    <x-text-input type="number" id="port" name="port" value="{{ old('port', $port) }}" placeholder="587" class="mt-1 block w-full" />
                                    @error('port')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                                </div>
                            </div>
                            <div>
                                <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                                <x-text-input type="text" id="username" name="username" value="{{ old('username', $username) }}" class="mt-1 block w-full" />
                                @error('username')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700">Kata Laluan</label>
                                <x-text-input type="password" id="password" name="password" placeholder="{{ $kataLaluanSudahDitetapkan ? '•••••••• (biar kosong untuk kekalkan)' : 'Masukkan kata laluan' }}" class="mt-1 block w-full" />
                                @error('password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="enkripsi" class="block text-sm font-medium text-gray-700">Enkripsi</label>
                                <x-select id="enkripsi" name="enkripsi" class="mt-1 block w-full">
                                    <option value="tls" {{ old('enkripsi', $enkripsi) === 'tls' ? 'selected' : '' }}>TLS</option>
                                    <option value="ssl" {{ old('enkripsi', $enkripsi) === 'ssl' ? 'selected' : '' }}>SSL</option>
                                    <option value="none" {{ old('enkripsi', $enkripsi) === 'none' ? 'selected' : '' }}>Tiada</option>
                                </x-select>
                                @error('enkripsi')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 border-t border-gray-100 pt-4">
                            <div>
                                <label for="alamat_dari" class="block text-sm font-medium text-gray-700">Alamat Emel Penghantar</label>
                                <x-text-input type="email" id="alamat_dari" name="alamat_dari" value="{{ old('alamat_dari', $alamatDari) }}" class="mt-1 block w-full" />
                                @error('alamat_dari')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="nama_dari" class="block text-sm font-medium text-gray-700">Nama Penghantar</label>
                                <x-text-input type="text" id="nama_dari" name="nama_dari" value="{{ old('nama_dari', $namaDari) }}" class="mt-1 block w-full" />
                                @error('nama_dari')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center justify-end border-t border-gray-200 bg-gray-50 px-4 py-3">
                        <button type="submit" class="inline-flex items-center gap-2 rounded-md bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-4 flex items-center justify-between">
                <div>
                    <h4 class="text-sm font-semibold text-gray-900">Uji Tetapan Emel</h4>
                    <p class="mt-1 text-xs text-gray-500">Hantar emel ujian ke alamat emel akaun anda ({{ auth()->user()->email }}) untuk sahkan tetapan berfungsi.</p>
                </div>
                <form method="POST" action="{{ route('tetapan.emel.ujian') }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 rounded-md bg-white border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">
                        <i class="fas fa-paper-plane"></i>
                        Hantar Emel Ujian
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
