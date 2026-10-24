<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Profil
        </h2>
    </x-slot>

    <div class="py-2">
        @role('Penjaga')
            <x-portal.tajuk-telefon tajuk="Akaun" subtajuk="Profil dan kata laluan" />
        @endrole

        <div class="max-w-5xl mx-auto sm:px-2 lg:px-2 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            {{-- Penjaga di telefon tiada sidebar, jadi log keluar diletakkan di sini. --}}
            @role('Penjaga')
                <form method="POST" action="{{ route('logout') }}" class="lg:hidden">
                    @csrf
                    <button type="submit" class="flex w-full items-center justify-center gap-2 rounded-full border border-red-200 bg-white py-3 text-[15px] font-semibold text-red-600 shadow-sm">
                        <i class="fas fa-right-from-bracket"></i> Log Keluar
                    </button>
                </form>
            @endrole

            {{-- Akaun Penjaga diurus oleh pihak sekolah; penjaga tidak boleh memadam akaun sendiri. --}}
            @unlessrole('Penjaga')
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <div class="max-w-xl">
                        @include('profile.partials.delete-user-form')
                    </div>
                </div>
            @endunlessrole
        </div>
    </div>
</x-app-layout>
