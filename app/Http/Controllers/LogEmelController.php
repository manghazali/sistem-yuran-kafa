<?php

namespace App\Http\Controllers;

use App\Models\LogEmel;
use Illuminate\Http\Request;

class LogEmelController extends Controller
{
    public function index(Request $request)
    {
        $query = LogEmel::query();

        if ($request->filled('kepada')) {
            $query->where('kepada', 'like', '%'.$request->input('kepada').'%');
        }

        if ($request->filled('subjek')) {
            $query->where('subjek', 'like', '%'.$request->input('subjek').'%');
        }

        [$urut, $arah] = $this->sahkanUrutan($request, ['created_at', 'kepada', 'subjek'], 'created_at', 'desc');

        $logEmel = $query->orderBy($urut, $arah)->paginate(10)->withQueryString();

        return view('log-emel.index', compact('logEmel', 'urut', 'arah'));
    }

    public function show(LogEmel $logEmel)
    {
        return view('log-emel.show', compact('logEmel'));
    }
}
