<x-app-layout>

    <div class="py-2">
        <div class="space-y-6">
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-900">
                    Tetapan Sistem
                </h3>
                <p class="mt-1 text-sm text-gray-500">
                    Urus konfigurasi dan tetapan sistem.
                </p>
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <a href="{{ route('tetapan.payment-gateway') }}" class="group rounded-lg border border-gray-200 bg-white p-4 shadow-sm transition hover:border-green-500 hover:shadow-md">
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-green-100 text-green-600">
                            <i class="fas fa-credit-card text-xl"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900 group-hover:text-green-600">
                                Gateway Pembayaran
                            </h4>
                            <p class="mt-1 text-sm text-gray-500">
                                Tetapan payment gateway
                            </p>
                        </div>
                    </div>
                </a>
                <a href="{{ route('tetapan.no-bayaran') }}" class="group rounded-lg border border-gray-200 bg-white p-4 shadow-sm transition hover:border-green-500 hover:shadow-md">
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600">
                            <i class="fas fa-file-invoice text-xl"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900 group-hover:text-green-600">
                                Generate No. Bayaran
                            </h4>
                            <p class="mt-1 text-sm text-gray-500">
                                Urus nombor rekod bayaran
                            </p>
                        </div>
                    </div>
                </a>
                <a href="{{ route('tetapan.no-resit') }}" class="group rounded-lg border border-gray-200 bg-white p-4 shadow-sm transition hover:border-green-500 hover:shadow-md">
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-blue-100 text-blue-600">
                            <i class="fas fa-receipt text-xl"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900 group-hover:text-green-600">
                                Generate No. Resit
                            </h4>
                            <p class="mt-1 text-sm text-gray-500">
                                Urus nombor resit
                            </p>
                        </div>
                    </div>
                </a>
                <a href="{{ route('tetapan.no-daftar') }}" class="group rounded-lg border border-gray-200 bg-white p-4 shadow-sm transition hover:border-green-500 hover:shadow-md">
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-purple-100 text-purple-600">
                            <i class="fas fa-id-card text-xl"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900 group-hover:text-green-600">
                                Generate No. Pendaftaran
                            </h4>
                            <p class="mt-1 text-sm text-gray-500">
                                Urus nombor pendaftaran pelajar
                            </p>
                        </div>
                    </div>
                </a>
                <a href="{{ route('tetapan.maklumat-sekolah') }}" class="group rounded-lg border border-gray-200 bg-white p-4 shadow-sm transition hover:border-green-500 hover:shadow-md">
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-purple-100 text-purple-600">
                            <i class="fas fa-school text-xl"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900 group-hover:text-green-600">
                                Maklumat Sekolah
                            </h4>
                            <p class="mt-1 text-sm text-gray-500">
                                Nama, alamat & telefon sekolah
                            </p>
                        </div>
                    </div>
                </a>
                <a href="{{ route('tetapan.emel') }}" class="group rounded-lg border border-gray-200 bg-white p-4 shadow-sm transition hover:border-green-500 hover:shadow-md">
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-orange-100 text-orange-600">
                            <i class="fas fa-envelope text-xl"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900 group-hover:text-green-600">
                                Tetapan Emel
                            </h4>
                            <p class="mt-1 text-sm text-gray-500">
                                Konfigurasi SMTP & penghantaran emel
                            </p>
                        </div>
                    </div>
                </a>
                <a href="{{ route('tetapan.emel-templat') }}" class="group rounded-lg border border-gray-200 bg-white p-4 shadow-sm transition hover:border-green-500 hover:shadow-md">
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-red-100 text-red-600">
                            <i class="fas fa-file-pen text-xl"></i>
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900 group-hover:text-green-600">
                                Templat Emel
                            </h4>
                            <p class="mt-1 text-sm text-gray-500">
                                Sunting kandungan emel akaun Guru & Penjaga
                            </p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>