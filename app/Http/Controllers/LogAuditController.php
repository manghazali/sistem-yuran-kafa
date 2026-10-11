<?php

namespace App\Http\Controllers;

use App\Models\LogAudit;
use App\Models\User;
use Illuminate\Http\Request;

class LogAuditController extends Controller
{
    public function index(Request $request)
    {
        $query = LogAudit::with('pengguna');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        if ($request->filled('model')) {
            $query->where('model', $request->input('model'));
        }

        if ($request->filled('tarikh_mula')) {
            $query->whereDate('created_at', '>=', $request->input('tarikh_mula'));
        }

        if ($request->filled('tarikh_akhir')) {
            $query->whereDate('created_at', '<=', $request->input('tarikh_akhir'));
        }

        [$urut, $arah] = $this->sahkanUrutan($request, ['created_at', 'tindakan', 'model', 'keterangan'], 'created_at', 'desc');

        $log = $query->orderBy($urut, $arah)->paginate(10)->withQueryString();
        $senaraiPengguna = User::orderBy('name')->get();
        $senaraiModel = LogAudit::select('model')->distinct()->whereNotNull('model')->pluck('model');

        return view('log-audit.index', compact('log', 'senaraiPengguna', 'senaraiModel', 'urut', 'arah'));
    }
}
