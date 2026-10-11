<div x-data="{
        yuranTerbuka: {{ request()->routeIs('kategori-yuran.*', 'struktur-yuran.*', 'tetapan-yuran-pelajar.*') ? 'true' : 'false' }},
        kewanganTerbuka: {{ request()->routeIs('bayaran.*', 'baki-tertunggak.*', 'resit.*', 'laporan.*', 'permohonan-bayaran.*') ? 'true' : 'false' }},
        yuranPenjagaTerbuka: {{ request()->routeIs('portal.yuran.*') ? 'true' : 'false' }},
     }"
     class="h-full flex flex-col bg-white text-gray-800 w-64 border-r border-gray-200">

    <div class="h-16 flex items-center px-6 border-b border-gray-200 flex-shrink-0">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
            <img src="{{ asset('images/logo.png') }}" alt="Logo Sekolah" class="h-11 w-11 object-contain">
            <span class="text-gray-800 font-semibold text-sm leading-tight">SISTEM YURAN<br>KAFA</span>
        </a>
    </div>

    <div class="px-6 pt-4 pb-2 text-center flex-shrink-0"
        x-data="{
            tarikhJawi: '',
            masaJawi: '',
            namaHariJawi: ['الأحد', 'الإثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'],
            namaBulanHijriJawi: ['محرم', 'صفر', 'ربيع الأول', 'ربيع الآخر', 'جمادى الأولى', 'جمادى الآخرة', 'رجب', 'شعبان', 'رمضان', 'شوال', 'ذو القعدة', 'ذو الحجة'],
            angkaArab: ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'],
            tukarAngka(n) {
                return String(n).split('').map(d => this.angkaArab[d] ?? d).join('');
            },
            tukarHijri(tarikh) {
                let hari = tarikh.getDate(), bulan = tarikh.getMonth() + 1, tahun = tarikh.getFullYear();
                let m = bulan, y = tahun;
                if (m < 3) { y -= 1; m += 12; }
                let a = Math.floor(y / 100);
                let b = 2 - a + Math.floor(a / 4);
                let jd = Math.floor(365.25 * (y + 4716)) + Math.floor(30.6001 * (m + 1)) + hari + b - 1524;
                let l = jd - 1948440 + 10632;
                let n = Math.floor((l - 1) / 10631);
                l = l - 10631 * n + 354;
                let j = Math.floor((10985 - l) / 5316) * Math.floor((50 * l) / 17719) + Math.floor(l / 5670) * Math.floor((43 * l) / 15238);
                l = l - Math.floor((30 - j) / 15) * Math.floor((17719 * j) / 50) - Math.floor(j / 16) * Math.floor((15238 * j) / 43) + 29;
                let hBulan = Math.floor((24 * l) / 709);
                let hHari = l - Math.floor((709 * hBulan) / 24);
                let hTahun = 30 * n + j - 30;
                return { hari: hHari, bulan: hBulan, tahun: hTahun };
            },
            kemaskini() {
                let sekarang = new Date();
                let h = this.tukarHijri(sekarang);
                let namaHari = this.namaHariJawi[sekarang.getDay()];
                let namaBulan = this.namaBulanHijriJawi[h.bulan - 1];
                this.tarikhJawi = namaHari + '، ' + this.tukarAngka(h.hari) + ' ' + namaBulan + ' ' + this.tukarAngka(h.tahun) + 'هـ';
                let j = sekarang.getHours(), i = sekarang.getMinutes(), s = sekarang.getSeconds();
                this.masaJawi = this.tukarAngka(String(j).padStart(2, '0')) + ':' + this.tukarAngka(String(i).padStart(2, '0')) + ':' + this.tukarAngka(String(s).padStart(2, '0'));
            },
            init() {
                this.kemaskini();
                setInterval(() => this.kemaskini(), 1000);
            }
        }"
        x-init="init()">
        <div dir="rtl" style="font-family: 'Noto Naskh Arabic', serif;">
            <p class="text-sm font-semibold text-gray-700 leading-tight" x-text="tarikhJawi"></p>
            <p class="text-sm text-gray-400" x-text="masaJawi"></p>
        </div>
    </div>

    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">

        @role('Penjaga')
            <a href="{{ route('portal.index') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium
                   {{ request()->routeIs('portal.index') ? 'bg-green-700 text-white font-semibold' : 'text-gray-800 hover:bg-green-100 hover:text-gray-600' }}">
                <i class="fas fa-house fa-fw"></i> {{ __('Portal Penjaga') }}
            </a>

            <!-- Kumpulan: Yuran (Penjaga) -->
            <div>
                <button @click="yuranPenjagaTerbuka = !yuranPenjagaTerbuka" type="button"
                        class="w-full flex items-center justify-between px-3 py-2 rounded-md text-sm font-medium text-gray-800 hover:bg-green-100 hover:text-gray-600">
                    <span class="flex items-center gap-3"><i class="fas fa-sack-dollar fa-fw"></i> {{ __('Yuran') }}</span>
                    <svg class="h-4 w-4 transition-transform" :class="{ 'rotate-90': yuranPenjagaTerbuka }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
                <div x-show="yuranPenjagaTerbuka" x-collapse class="mt-1 ml-6 space-y-1">
                    <a href="{{ route('portal.yuran.semua') }}"
                       class="block px-3 py-1.5 rounded-md text-sm {{ request()->routeIs('portal.yuran.semua') ? 'bg-green-700 text-white font-semibold' : 'text-gray-800 hover:bg-green-100 hover:text-gray-600' }}">
                        {{ __('Semua Yuran') }}
                    </a>
                    <a href="{{ route('portal.yuran.belum-bayar') }}"
                       class="block px-3 py-1.5 rounded-md text-sm {{ request()->routeIs('portal.yuran.belum-bayar') ? 'bg-green-700 text-white font-semibold' : 'text-gray-800 hover:bg-green-100 hover:text-gray-600' }}">
                        {{ __('Yuran Belum Bayar') }}
                    </a>
                    <a href="{{ route('portal.yuran.bulanan') }}"
                       class="block px-3 py-1.5 rounded-md text-sm {{ request()->routeIs('portal.yuran.bulanan') ? 'bg-green-700 text-white font-semibold' : 'text-gray-800 hover:bg-green-100 hover:text-gray-600' }}">
                        {{ __('Yuran Bulanan') }}
                    </a>
                </div>
            </div>

            <a href="{{ route('portal.status-pembayaran') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium
                   {{ request()->routeIs('portal.status-pembayaran') ? 'bg-green-700 text-white font-semibold' : 'text-gray-800 hover:bg-green-100 hover:text-gray-600' }}">
                <i class="fas fa-credit-card fa-fw"></i> {{ __('Status Pembayaran') }}
            </a>

            <a href="{{ route('portal.sejarah-bayaran') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium
                   {{ request()->routeIs('portal.sejarah-bayaran') ? 'bg-green-700 text-white font-semibold' : 'text-gray-800 hover:bg-green-100 hover:text-gray-600' }}">
                <i class="fas fa-clock-rotate-left fa-fw"></i> {{ __('Sejarah Bayaran') }}
            </a>
        @endrole

        @hasanyrole('Admin|Akauntan|Guru')
            <a href="{{ route('dashboard') }}"
            class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium
                {{ request()->routeIs('dashboard') || request()->routeIs('dashboard.*') ? 'bg-green-700 text-white font-semibold' : 'text-gray-800 hover:bg-green-100 hover:text-gray-600' }}">
                <i class="fas fa-house fa-fw"></i> {{ __('Papan Pemuka') }}
            </a>
        @endhasanyrole

        @hasanyrole('Admin|Guru')
            <a href="{{ route('pelajar.index') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium
                   {{ request()->routeIs('pelajar.*') ? 'bg-green-700 text-white font-semibold' : 'text-gray-800 hover:bg-green-100 hover:text-gray-600' }}">
                <i class="fas fa-graduation-cap fa-fw"></i> {{ __('Pelajar') }}
            </a>
            <a href="{{ route('kelas.index') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium
                   {{ request()->routeIs('kelas.*') ? 'bg-green-700 text-white font-semibold' : 'text-gray-800 hover:bg-green-100 hover:text-gray-600' }}">
                <i class="fas fa-school fa-fw"></i> {{ __('Kelas') }}
            </a>
        @endhasanyrole

        @role('Guru')
            <a href="{{ route('permohonan-bayaran.index') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium
                   {{ request()->routeIs('permohonan-bayaran.*') ? 'bg-green-700 text-white font-semibold' : 'text-gray-800 hover:bg-green-100 hover:text-gray-600' }}">
                <i class="fas fa-hand-holding-dollar fa-fw"></i> {{ __('Permohonan Bayaran') }}
            </a>
        @endrole

        @role('Admin')
            <a href="{{ route('penjaga.index') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium
                   {{ request()->routeIs('penjaga.*') ? 'bg-green-700 text-white font-semibold' : 'text-gray-800 hover:bg-green-100 hover:text-gray-600' }}">
                <i class="fas fa-people-group fa-fw"></i> {{ __('Penjaga') }}
            </a>
            <a href="{{ route('guru.index') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium
                   {{ request()->routeIs('guru.*') ? 'bg-green-700 text-white font-semibold' : 'text-gray-800 hover:bg-green-100 hover:text-gray-600' }}">
                <i class="fas fa-chalkboard-user fa-fw"></i> {{ __('Guru') }}
            </a>
            <a href="{{ route('pengguna.index') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium
                   {{ request()->routeIs('pengguna.*') ? 'bg-green-700 text-white font-semibold' : 'text-gray-800 hover:bg-green-100 hover:text-gray-600' }}">
                <i class="fas fa-users-gear fa-fw"></i> {{ __('Pengurusan Pengguna') }}
            </a>
        @endrole

        @hasanyrole('Admin|Akauntan')
            <!-- Kumpulan: Yuran -->
            <div>
                <button @click="yuranTerbuka = !yuranTerbuka" type="button"
                        class="w-full flex items-center justify-between px-3 py-2 rounded-md text-sm font-medium text-gray-800 hover:bg-green-100 hover:text-gray-600">
                    <span class="flex items-center gap-3"><i class="fas fa-credit-card fa-fw"></i> {{ __('Yuran') }}</span>
                    <svg class="h-4 w-4 transition-transform" :class="{ 'rotate-90': yuranTerbuka }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
                <div x-show="yuranTerbuka" x-collapse class="mt-1 ml-6 space-y-1">
                    <a href="{{ route('kategori-yuran.index') }}"
                       class="block px-3 py-1.5 rounded-md text-sm {{ request()->routeIs('kategori-yuran.*') ? 'bg-green-700 text-white font-semibold' : 'text-gray-800 hover:bg-green-100 hover:text-gray-600' }}">
                        {{ __('Kategori Yuran') }}
                    </a>
                    <a href="{{ route('struktur-yuran.index') }}"
                       class="block px-3 py-1.5 rounded-md text-sm {{ request()->routeIs('struktur-yuran.*') ? 'bg-green-700 text-white font-semibold' : 'text-gray-800 hover:bg-green-100 hover:text-gray-600' }}">
                        {{ __('Struktur Yuran') }}
                    </a>
                    <a href="{{ route('tetapan-yuran-pelajar.index') }}"
                       class="block px-3 py-1.5 rounded-md text-sm {{ request()->routeIs('tetapan-yuran-pelajar.*') ? 'bg-green-700 text-white font-semibold' : 'text-gray-800 hover:bg-green-100 hover:text-gray-600' }}">
                        {{ __('Tetapan Yuran Pelajar') }}
                    </a>
                </div>
            </div>

            <!-- Kumpulan: Kewangan -->
            <div>
                <button @click="kewanganTerbuka = !kewanganTerbuka" type="button"
                        class="w-full flex items-center justify-between px-3 py-2 rounded-md text-sm font-medium text-gray-800 hover:bg-green-100 hover:text-gray-600">
                    <span class="flex items-center gap-3"><i class="fas fa-chart-line fa-fw"></i> {{ __('Kewangan') }}</span>
                    <svg class="h-4 w-4 transition-transform" :class="{ 'rotate-90': kewanganTerbuka }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
                <div x-show="kewanganTerbuka" x-collapse class="mt-1 ml-6 space-y-1">
                    <a href="{{ route('bayaran.index') }}"
                       class="block px-3 py-1.5 rounded-md text-sm {{ request()->routeIs('bayaran.*') ? 'bg-green-700 text-white font-semibold' : 'text-gray-800 hover:bg-green-100 hover:text-gray-600' }}">
                        {{ __('Bayaran') }}
                    </a>
                    <a href="{{ route('baki-tertunggak.index') }}"
                       class="block px-3 py-1.5 rounded-md text-sm {{ request()->routeIs('baki-tertunggak.*') ? 'bg-green-700 text-white font-semibold' : 'text-gray-800 hover:bg-green-100 hover:text-gray-600' }}">
                        {{ __('Baki Belum Dibayar') }}
                    </a>
                    <a href="{{ route('permohonan-bayaran.index') }}"
                       class="block px-3 py-1.5 rounded-md text-sm {{ request()->routeIs('permohonan-bayaran.*') ? 'bg-green-700 text-white font-semibold' : 'text-gray-800 hover:bg-green-100 hover:text-gray-600' }}">
                        {{ __('Permohonan Bayaran') }}
                    </a>
                    @if (\Illuminate\Support\Facades\Route::has('laporan.index'))
                        <a href="{{ route('laporan.index') }}"
                           class="block px-3 py-1.5 rounded-md text-sm {{ request()->routeIs('laporan.*') ? 'bg-green-700 text-white font-semibold' : 'text-gray-800 hover:bg-green-100 hover:text-gray-600' }}">
                            {{ __('Laporan') }}
                        </a>
                    @endif
                </div>
            </div>
        @endhasanyrole

        @role('Admin')
            @if (\Illuminate\Support\Facades\Route::has('log-audit.index'))
                <a href="{{ route('log-audit.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium
                       {{ request()->routeIs('log-audit.*') ? 'bg-green-700 text-white font-semibold' : 'text-gray-800 hover:bg-green-100 hover:text-gray-600' }}">
                    <i class="fas fa-magnifying-glass fa-fw"></i> {{ __('Log Audit') }}
                </a>
            @endif
            @if (\Illuminate\Support\Facades\Route::has('log-emel.index'))
                <a href="{{ route('log-emel.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium
                       {{ request()->routeIs('log-emel.*') ? 'bg-green-700 text-white font-semibold' : 'text-gray-800 hover:bg-green-100 hover:text-gray-600' }}">
                    <i class="fas fa-envelope fa-fw"></i> {{ __('Log Emel') }}
                </a>
            @endif
            @if (\Illuminate\Support\Facades\Route::has('tetapan.index'))
                <a href="{{ route('tetapan.index') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium
                       {{ request()->routeIs('tetapan.*') ? 'bg-green-700 text-white font-semibold' : 'text-gray-800 hover:bg-green-100 hover:text-gray-600' }}">
                    <i class="fas fa-gear fa-fw"></i> {{ __('Tetapan') }}
                </a>
            @endif
        @endrole
    </nav>

    <!-- User / logout -->
    <div class="border-t border-gray-200 p-3 flex-shrink-0">
        <div x-data="{ terbuka: false }" class="relative">
            <button @click="terbuka = !terbuka" @click.away="terbuka = false" type="button"
                    class="w-full flex items-center gap-3 px-3 py-2 rounded-md text-sm text-gray-800 hover:bg-green-100 hover:text-gray-600">
                <div class="h-8 w-8 rounded-full bg-gray-700 flex items-center justify-center text-white text-xs font-semibold flex-shrink-0">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
                <span class="truncate flex-1 text-left">{{ Auth::user()->name }}</span>
                <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                </svg>
            </button>

            <div x-show="terbuka" x-transition style="display: none;"
                 class="absolute bottom-full left-0 mb-2 w-full bg-white rounded-md shadow-lg py-1 z-50">
                <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                    {{ __('Profil') }}
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        {{ __('Log Keluar') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>