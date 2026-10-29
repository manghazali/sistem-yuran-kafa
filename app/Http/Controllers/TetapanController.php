<?php

namespace App\Http\Controllers;

use App\Models\Bayaran;
use App\Models\Pelajar;
use App\Models\Tetapan;
use App\Services\PaymentGatewayService;
use App\Support\EmelTemplat;
use Illuminate\Http\Request;

class TetapanController extends Controller
{
    public function index()
    {
        return view('tetapan.index');
    }

    public function paymentGateway()
    {
        $merchantId = Tetapan::get('payment_gateway_merchant_id', config('payment_gateway.merchant_id'));
        $sandbox = Tetapan::get('payment_gateway_sandbox', config('payment_gateway.sandbox') ? '1' : '0');
        $secretKeySudahDitetapkan = (bool) Tetapan::get('payment_gateway_secret_key', config('payment_gateway.secret_key'));
        $urlNotifikasi = route('payment-gateway.callback').'?token='.app(PaymentGatewayService::class)->webhookToken();

        return view('tetapan.payment-gateway', compact('merchantId', 'sandbox', 'secretKeySudahDitetapkan', 'urlNotifikasi'));
    }

    public function kemaskiniPaymentGateway(Request $request)
    {
        $validated = $request->validate([
            'merchant_id' => ['required', 'string', 'max:255'],
            'secret_key' => ['nullable', 'string', 'max:255'],
        ]);

        Tetapan::set('payment_gateway_merchant_id', $validated['merchant_id']);

        // Secret key hanya dikemaskini jika Admin isi nilai baharu —
        // biar kosong untuk KEKALKAN nilai sedia ada (elak terpadam
        // tanpa sengaja).
        if (! empty($validated['secret_key'])) {
            Tetapan::set('payment_gateway_secret_key', $validated['secret_key']);
        }

        Tetapan::set('payment_gateway_sandbox', $request->boolean('sandbox') ? '1' : '0');

        return redirect()->route('tetapan.payment-gateway')->with('status', 'Tetapan Gerbang Pembayaran berjaya dikemaskini.');
    }

    public function maklumatSekolah()
    {
        $nama = Tetapan::get('sekolah_nama', "KAFA Integrasi Darus Sa'adah");
        $alamat = Tetapan::get('sekolah_alamat', "Jalan DC 2/4, Desa Coalfields,\n47000 Sungai Buloh,\nSelangor");
        $telefon = Tetapan::get('sekolah_telefon', '03-6038 8306');

        return view('tetapan.maklumat-sekolah', compact('nama', 'alamat', 'telefon'));
    }

    public function kemaskiniMaklumatSekolah(Request $request)
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'alamat' => ['required', 'string', 'max:500'],
            'telefon' => ['required', 'string', 'max:50'],
        ]);

        Tetapan::set('sekolah_nama', $validated['nama']);
        Tetapan::set('sekolah_alamat', $validated['alamat']);
        Tetapan::set('sekolah_telefon', $validated['telefon']);

        return redirect()->route('tetapan.maklumat-sekolah')->with('status', 'Maklumat Sekolah berjaya dikemaskini.');
    }

    public function emel()
    {
        $driver = Tetapan::get('emel_driver', config('mail.default'));
        $host = Tetapan::get('emel_host', config('mail.mailers.smtp.host'));
        $port = Tetapan::get('emel_port', config('mail.mailers.smtp.port'));
        $username = Tetapan::get('emel_username', config('mail.mailers.smtp.username'));
        $kataLaluanSudahDitetapkan = (bool) Tetapan::get('emel_password');
        $enkripsi = Tetapan::get('emel_enkripsi', 'tls');
        $alamatDari = Tetapan::get('emel_alamat_dari', config('mail.from.address'));
        $namaDari = Tetapan::get('emel_nama_dari', config('mail.from.name'));

        return view('tetapan.emel', compact(
            'driver', 'host', 'port', 'username', 'kataLaluanSudahDitetapkan',
            'enkripsi', 'alamatDari', 'namaDari'
        ));
    }

    public function kemaskiniEmel(Request $request)
    {
        $validated = $request->validate([
            'driver' => ['required', 'in:log,smtp'],
            'host' => ['nullable', 'string', 'max:255'],
            'port' => ['nullable', 'integer'],
            'username' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'max:255'],
            'enkripsi' => ['nullable', 'in:tls,ssl,none'],
            'alamat_dari' => ['required', 'email', 'max:255'],
            'nama_dari' => ['required', 'string', 'max:255'],
        ]);

        Tetapan::set('emel_driver', $validated['driver']);
        Tetapan::set('emel_host', $validated['host'] ?? '');
        Tetapan::set('emel_port', $validated['port'] ?? '');
        Tetapan::set('emel_username', $validated['username'] ?? '');

        // Kata laluan hanya dikemaskini jika Admin isi nilai baharu —
        // biar kosong untuk KEKALKAN nilai sedia ada.
        if (! empty($validated['password'])) {
            Tetapan::set('emel_password', $validated['password']);
        }

        Tetapan::set('emel_enkripsi', $validated['enkripsi'] ?? 'tls');
        Tetapan::set('emel_alamat_dari', $validated['alamat_dari']);
        Tetapan::set('emel_nama_dari', $validated['nama_dari']);

        return redirect()->route('tetapan.emel')->with('status', 'Tetapan Emel berjaya dikemaskini.');
    }

    public function hantarEmelUjian(Request $request)
    {
        try {
            \Illuminate\Support\Facades\Mail::raw(
                'Ini adalah emel ujian daripada Sistem Pengurusan Yuran Pelajar KAFA. Jika anda menerima emel ini, tetapan emel anda berfungsi dengan baik.',
                fn ($message) => $message->to($request->user()->email)->subject('Emel Ujian - Sistem Yuran KAFA')
            );

            return back()->with('status', 'Emel ujian berjaya dihantar ke '.$request->user()->email.'.');
        } catch (\Throwable $e) {
            return back()->withErrors(['emel' => 'Gagal menghantar emel ujian: '.$e->getMessage()]);
        }
    }

    public function emelTemplat()
    {
        $templat = collect(EmelTemplat::DEFAULT_TEMPLAT)->map(fn ($lalai, $kunci) => [
            'label' => $lalai['label'],
            'keterangan' => $lalai['keterangan'],
            'subjek' => EmelTemplat::subjek($kunci),
            'mesej' => EmelTemplat::mesej($kunci),
            'placeholder' => $lalai['placeholder'],
        ]);

        return view('tetapan.emel-templat', compact('templat'));
    }

    public function kemaskiniEmelTemplat(Request $request, string $templat)
    {
        abort_unless(array_key_exists($templat, EmelTemplat::DEFAULT_TEMPLAT), 404);

        $validated = $request->validate([
            'subjek' => ['required', 'string', 'max:255'],
            'mesej' => ['required', 'string', 'max:3000'],
        ]);

        Tetapan::set("emel_templat_{$templat}_subjek", $validated['subjek']);
        Tetapan::set("emel_templat_{$templat}_mesej", $validated['mesej']);

        return redirect()->route('tetapan.emel-templat')->with('status', 'Templat emel "'.EmelTemplat::DEFAULT_TEMPLAT[$templat]['label'].'" berjaya dikemaskini.');
    }

    public function noBayaran()
    {
        $format = Tetapan::get('no_bayaran_format', 'BYR/{TAHUN}/{NOMBOR}');
        $tahun = (int) Tetapan::get('no_bayaran_tahun', now()->year);
        $nomborSemasa = (int) Tetapan::get('no_bayaran_nombor_semasa', 0);
        $preview = Bayaran::formatNombor($format, $tahun, $nomborSemasa + 1);

        return view('tetapan.no-bayaran', compact('format', 'tahun', 'nomborSemasa', 'preview'));
    }

    public function kemaskiniNoBayaran(Request $request)
    {
        $validated = $request->validate([
            'format' => ['required', 'string', 'max:255'],
            'tahun' => ['required', 'integer', 'min:2000', 'max:2100'],
            'nombor_semasa' => ['required', 'integer', 'min:0'],
        ]);

        Tetapan::set('no_bayaran_format', $validated['format']);
        Tetapan::set('no_bayaran_tahun', (string) $validated['tahun']);
        Tetapan::set('no_bayaran_nombor_semasa', (string) $validated['nombor_semasa']);

        return redirect()->route('tetapan.no-bayaran')->with('status', 'Tetapan No. Bayaran berjaya dikemaskini.');
    }

    public function noResit()
    {
        $format = Tetapan::get('no_resit_format', 'RESIT/{TAHUN}/{NOMBOR}');
        $tahun = (int) Tetapan::get('no_resit_tahun', now()->year);
        $nomborSemasa = (int) Tetapan::get('no_resit_nombor_semasa', 0);
        $preview = Bayaran::formatNombor($format, $tahun, $nomborSemasa + 1);

        return view('tetapan.no-resit', compact('format', 'tahun', 'nomborSemasa', 'preview'));
    }

    public function kemaskiniNoResit(Request $request)
    {
        $validated = $request->validate([
            'format' => ['required', 'string', 'max:255'],
            'tahun' => ['required', 'integer', 'min:2000', 'max:2100'],
            'nombor_semasa' => ['required', 'integer', 'min:0'],
        ]);

        Tetapan::set('no_resit_format', $validated['format']);
        Tetapan::set('no_resit_tahun', (string) $validated['tahun']);
        Tetapan::set('no_resit_nombor_semasa', (string) $validated['nombor_semasa']);

        return redirect()->route('tetapan.no-resit')->with('status', 'Tetapan No. Resit berjaya dikemaskini.');
    }

    public function noDaftar()
    {
        $format = Tetapan::get('no_daftar_format', 'PLJ/{TAHUN}/{NOMBOR}');
        $tahun = (int) Tetapan::get('no_daftar_tahun', now()->year);
        $nomborSemasa = (int) Tetapan::get('no_daftar_nombor_semasa', 0);
        $preview = Pelajar::formatNombor($format, $tahun, $nomborSemasa + 1);

        return view('tetapan.no-daftar', compact('format', 'tahun', 'nomborSemasa', 'preview'));
    }

    public function kemaskiniNoDaftar(Request $request)
    {
        $validated = $request->validate([
            'format' => ['required', 'string', 'max:255'],
            'tahun' => ['required', 'integer', 'min:2000', 'max:2100'],
            'nombor_semasa' => ['required', 'integer', 'min:0'],
        ]);

        Tetapan::set('no_daftar_format', $validated['format']);
        Tetapan::set('no_daftar_tahun', (string) $validated['tahun']);
        Tetapan::set('no_daftar_nombor_semasa', (string) $validated['nombor_semasa']);

        return redirect()->route('tetapan.no-daftar')->with('status', 'Tetapan No. Pendaftaran berjaya dikemaskini.');
    }
}
