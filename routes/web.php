<?php

use App\Http\Controllers\BakiTertunggakController;
use App\Http\Controllers\BayaranController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GuruAkaunController;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\KategoriYuranController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\LogAuditController;
use App\Http\Controllers\LogEmelController;
use App\Http\Controllers\NaikDarjahController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\PaymentGatewayController;
use App\Http\Controllers\PelajarController;
use App\Http\Controllers\PenggunaController;
use App\Http\Controllers\PenjagaAkaunController;
use App\Http\Controllers\PenjagaController;
use App\Http\Controllers\PermohonanBayaranController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\PortalKalendarController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ResitController;
use App\Http\Controllers\StrukturYuranController;
use App\Http\Controllers\TetapanController;
use App\Http\Controllers\TetapanYuranPelajarController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/admin', [DashboardController::class, 'admin'])->middleware('role:Admin')->name('dashboard.admin');
    Route::get('/dashboard/akauntan', [DashboardController::class, 'akauntan'])->middleware('role:Akauntan')->name('dashboard.akauntan');
    Route::get('/dashboard/guru', [DashboardController::class, 'guru'])->middleware('role:Guru')->name('dashboard.guru');
});

Route::middleware('auth')->group(function () {
    Route::post('/notifikasi/baca-semua', [NotifikasiController::class, 'bacaSemua'])->name('notifikasi.baca-semua');
    Route::post('/notifikasi/{notifikasi}/baca', [NotifikasiController::class, 'baca'])->name('notifikasi.baca');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware('role:Admin')->group(function () {
        Route::get('/guru-import', [GuruController::class, 'import'])->name('guru.import');
        Route::post('/guru-import', [GuruController::class, 'importStore'])->name('guru.import.store');
        Route::get('/guru-import/template', [GuruController::class, 'importTemplate'])->name('guru.import.template');
    });
    Route::resource('guru', GuruController::class)->middleware('role:Admin');
    Route::post('/guru/{guru}/akaun', [GuruAkaunController::class, 'store'])->middleware('role:Admin')->name('guru.akaun.store');

    Route::middleware('role:Admin')->group(function () {
        Route::get('/penjaga-import', [PenjagaController::class, 'import'])->name('penjaga.import');
        Route::post('/penjaga-import', [PenjagaController::class, 'importStore'])->name('penjaga.import.store');
        Route::get('/penjaga-import/template', [PenjagaController::class, 'importTemplate'])->name('penjaga.import.template');
    });
    Route::resource('penjaga', PenjagaController::class)->middleware('role:Admin');

    Route::resource('pengguna', PenggunaController::class)->except(['show'])->middleware('role:Admin');
    Route::post('/pengguna/{pengguna}/reset-kata-laluan', [PenggunaController::class, 'resetKataLaluan'])->middleware('role:Admin')->name('pengguna.reset-kata-laluan');
    // Guru boleh lihat/tambah Kelas, tapi KEMASKINI dan PADAM khusus Admin
    // sahaja.
    Route::resource('kelas', KelasController::class)->parameters(['kelas' => 'kelas'])->except(['destroy', 'edit', 'update'])->middleware('role:Admin,Guru');
    Route::get('/kelas/{kelas}/edit', [KelasController::class, 'edit'])->middleware('role:Admin')->name('kelas.edit');
    Route::match(['put', 'patch'], '/kelas/{kelas}', [KelasController::class, 'update'])->middleware('role:Admin')->name('kelas.update');
    Route::delete('/kelas/{kelas}', [KelasController::class, 'destroy'])->middleware('role:Admin')->name('kelas.destroy');

    Route::middleware('role:Admin,Guru')->group(function () {
        Route::get('/pelajar-import', [PelajarController::class, 'import'])->name('pelajar.import');
        Route::post('/pelajar-import', [PelajarController::class, 'importStore'])->name('pelajar.import.store');
        Route::get('/pelajar-import/template', [PelajarController::class, 'importTemplate'])->name('pelajar.import.template');
    });
    // Guru boleh lihat/tambah Pelajar, tapi KEMASKINI dan PADAM khusus
    // Admin sahaja.
    Route::resource('pelajar', PelajarController::class)->except(['destroy', 'edit', 'update'])->middleware('role:Admin,Guru');
    Route::get('/pelajar/{pelajar}/edit', [PelajarController::class, 'edit'])->middleware('role:Admin')->name('pelajar.edit');
    Route::match(['put', 'patch'], '/pelajar/{pelajar}', [PelajarController::class, 'update'])->middleware('role:Admin')->name('pelajar.update');
    Route::delete('/pelajar/{pelajar}', [PelajarController::class, 'destroy'])->middleware('role:Admin')->name('pelajar.destroy');
    Route::get('/naik-darjah', [NaikDarjahController::class, 'index'])->middleware('role:Admin')->name('naik-darjah.index');
    Route::post('/naik-darjah', [NaikDarjahController::class, 'store'])->middleware('role:Admin')->name('naik-darjah.store');
    Route::resource('kategori-yuran', KategoriYuranController::class)->middleware('role:Admin,Akauntan');
    Route::resource('struktur-yuran', StrukturYuranController::class)->middleware('role:Admin,Akauntan');
    Route::middleware('role:Admin,Akauntan')->group(function () {
        Route::get('/tetapan-yuran-pelajar-pukal', [TetapanYuranPelajarController::class, 'pukal'])->name('tetapan-yuran-pelajar.pukal');
        Route::post('/tetapan-yuran-pelajar-pukal/pratonton', [TetapanYuranPelajarController::class, 'pukalPratonton'])->name('tetapan-yuran-pelajar.pukal.pratonton');
        Route::post('/tetapan-yuran-pelajar-pukal', [TetapanYuranPelajarController::class, 'pukalStore'])->name('tetapan-yuran-pelajar.pukal.store');
    });
    Route::resource('tetapan-yuran-pelajar', TetapanYuranPelajarController::class)->middleware('role:Admin,Akauntan');

    Route::get('/baki-tertunggak', [BakiTertunggakController::class, 'index'])->middleware('role:Admin,Akauntan')->name('baki-tertunggak.index');
    Route::get('/baki-tertunggak/csv', [BakiTertunggakController::class, 'csv'])->middleware('role:Admin,Akauntan')->name('baki-tertunggak.csv');
    Route::get('/baki-tertunggak/pdf', [BakiTertunggakController::class, 'pdf'])->middleware('role:Admin,Akauntan')->name('baki-tertunggak.pdf');

    Route::resource('bayaran', BayaranController::class)->only(['index', 'create', 'store', 'show', 'destroy'])->middleware('role:Admin,Akauntan');
    Route::get('/resit/{bayaran}', [ResitController::class, 'show'])->middleware('role:Admin,Akauntan,Penjaga')->name('resit.show');

    Route::get('/permohonan-bayaran', [PermohonanBayaranController::class, 'index'])->middleware('role:Admin,Akauntan,Guru')->name('permohonan-bayaran.index');
    Route::get('/permohonan-bayaran/create', [PermohonanBayaranController::class, 'create'])->middleware('role:Guru')->name('permohonan-bayaran.create');
    Route::post('/permohonan-bayaran', [PermohonanBayaranController::class, 'store'])->middleware('role:Guru')->name('permohonan-bayaran.store');
    Route::post('/permohonan-bayaran/{permohonanBayaran}/tolak', [PermohonanBayaranController::class, 'tolak'])->middleware('role:Admin,Akauntan')->name('permohonan-bayaran.tolak');
    Route::post('/penjaga/{penjaga}/akaun', [PenjagaAkaunController::class, 'store'])->middleware('role:Admin')->name('penjaga.akaun.store');

    Route::prefix('portal')->middleware('role:Penjaga')->group(function () {
        Route::get('/', [PortalController::class, 'index'])->name('portal.index');

        Route::get('/yuran/semua', [PortalController::class, 'yuranSemua'])->name('portal.yuran.semua');
        Route::get('/yuran/belum-bayar', [PortalController::class, 'yuranBelumBayar'])->name('portal.yuran.belum-bayar');
        Route::get('/yuran/bulanan', [PortalController::class, 'yuranBulanan'])->name('portal.yuran.bulanan');

        Route::redirect('/status-pembayaran', '/portal/sejarah-bayaran');

        Route::get('/sejarah-bayaran', [PortalController::class, 'sejarahBayaran'])->name('portal.sejarah-bayaran');
        Route::view('/bantuan', 'portal.bantuan')->name('portal.bantuan');

        Route::get('/bayar', [PortalKalendarController::class, 'semuaAnak'])->name('portal.bayar-semua');
        Route::get('/bayar/semak', [PortalKalendarController::class, 'semuaAnakSemak'])->name('portal.bayar-semua.semak');
        Route::post('/bayar', [PortalKalendarController::class, 'semuaAnakBayar'])->name('portal.bayar-semua.bayar');

        Route::get('/anak/{pelajar}', [PortalKalendarController::class, 'lihat'])->name('portal.anak');
        // Pautan lama (cth. dalam notifikasi sedia ada) ke kalendar per anak — kini satu aliran bayar sahaja.
        Route::redirect('/kalendar/{pelajar}', '/portal/bayar');
    });

    Route::get('/laporan', [LaporanController::class, 'index'])->middleware('role:Admin,Akauntan')->name('laporan.index');
    Route::get('/laporan/kutipan', [LaporanController::class, 'kutipan'])->middleware('role:Admin,Akauntan')->name('laporan.kutipan');
    Route::get('/laporan/kutipan/csv', [LaporanController::class, 'kutipanCsv'])->middleware('role:Admin,Akauntan')->name('laporan.kutipan.csv');
    Route::get('/laporan/kutipan/pdf', [LaporanController::class, 'kutipanPdf'])->middleware('role:Admin,Akauntan')->name('laporan.kutipan.pdf');

    Route::get('/log-audit', [LogAuditController::class, 'index'])->middleware('role:Admin')->name('log-audit.index');

    Route::get('/log-emel', [LogEmelController::class, 'index'])->middleware('role:Admin')->name('log-emel.index');
    Route::get('/log-emel/{logEmel}', [LogEmelController::class, 'show'])->middleware('role:Admin')->name('log-emel.show');

    Route::get('/tetapan', [TetapanController::class, 'index'])->middleware('role:Admin')->name('tetapan.index');
    Route::get('/tetapan/payment-gateway', [TetapanController::class, 'paymentGateway'])->middleware('role:Admin')->name('tetapan.payment-gateway');
    Route::put('/tetapan/payment-gateway', [TetapanController::class, 'kemaskiniPaymentGateway'])->middleware('role:Admin')->name('tetapan.payment-gateway.update');
    Route::get('/tetapan/no-bayaran', [TetapanController::class, 'noBayaran'])->middleware('role:Admin')->name('tetapan.no-bayaran');
    Route::put('/tetapan/no-bayaran', [TetapanController::class, 'kemaskiniNoBayaran'])->middleware('role:Admin')->name('tetapan.no-bayaran.update');
    Route::get('/tetapan/no-resit', [TetapanController::class, 'noResit'])->middleware('role:Admin')->name('tetapan.no-resit');
    Route::put('/tetapan/no-resit', [TetapanController::class, 'kemaskiniNoResit'])->middleware('role:Admin')->name('tetapan.no-resit.update');
    Route::get('/tetapan/no-daftar', [TetapanController::class, 'noDaftar'])->middleware('role:Admin')->name('tetapan.no-daftar');
    Route::put('/tetapan/no-daftar', [TetapanController::class, 'kemaskiniNoDaftar'])->middleware('role:Admin')->name('tetapan.no-daftar.update');
    Route::get('/tetapan/maklumat-sekolah', [TetapanController::class, 'maklumatSekolah'])->middleware('role:Admin')->name('tetapan.maklumat-sekolah');
    Route::put('/tetapan/maklumat-sekolah', [TetapanController::class, 'kemaskiniMaklumatSekolah'])->middleware('role:Admin')->name('tetapan.maklumat-sekolah.update');
    Route::get('/tetapan/emel', [TetapanController::class, 'emel'])->middleware('role:Admin')->name('tetapan.emel');
    Route::put('/tetapan/emel', [TetapanController::class, 'kemaskiniEmel'])->middleware('role:Admin')->name('tetapan.emel.update');
    Route::post('/tetapan/emel/ujian', [TetapanController::class, 'hantarEmelUjian'])->middleware('role:Admin')->name('tetapan.emel.ujian');
    Route::get('/tetapan/emel-templat', [TetapanController::class, 'emelTemplat'])->middleware('role:Admin')->name('tetapan.emel-templat');
    Route::put('/tetapan/emel-templat/{templat}', [TetapanController::class, 'kemaskiniEmelTemplat'])->middleware('role:Admin')->name('tetapan.emel-templat.update');
});

// Perlu log masuk: halaman ini memaparkan nama anak, amaun dan pautan resit.
Route::get('/payment-gateway/selesai', [PaymentGatewayController::class, 'selesai'])->middleware('auth')->name('payment-gateway.selesai');
Route::match(['get', 'post'], '/payment-gateway/callback', [PaymentGatewayController::class, 'callback'])->name('payment-gateway.callback');

require __DIR__.'/auth.php';
