<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PelajarTemplateExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return [
            'No Daftar',
            'Nama',
            'No KP',
            'Tarikh Lahir',
            'Jantina',
            'Kelas',
            'Tahun',
            'No KP Penjaga',
            'Status',
        ];
    }

    public function array(): array
    {
        return [
            [
                'KAFA-2026-001',
                'Ahmad bin Ali',
                '120101-01-0001',
                '01/01/2012',
                'Lelaki',
                '1 Ibnu Sina',
                'Tahun 5',
                '850101-01-0001',
                'Aktif',
            ],
        ];
    }
}
