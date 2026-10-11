<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

abstract class Controller
{
    /**
     * Sahkan parameter 'urut' (lajur) dan 'arah' (asc/desc) daripada request
     * terhadap senarai lajur yang dibenarkan — mengelakkan nama lajur sewenang-
     * wenangnya daripada pengguna disuntik terus ke dalam orderBy().
     *
     * @param  array<int, string>  $lajurDibenarkan
     * @return array{0: string, 1: string} [$lajur, $arah]
     */
    protected function sahkanUrutan(Request $request, array $lajurDibenarkan, string $lajurLalai, string $arahLalai = 'asc'): array
    {
        $lajur = $request->string('urut')->toString();
        $lajur = in_array($lajur, $lajurDibenarkan, true) ? $lajur : $lajurLalai;

        $arah = $request->string('arah')->toString();
        $arah = in_array($arah, ['asc', 'desc'], true) ? $arah : $arahLalai;

        return [$lajur, $arah];
    }
}
