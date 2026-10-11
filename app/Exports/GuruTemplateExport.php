<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class GuruTemplateExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return [
            'No Pekerja',
            'Nama',
            'No KP',
            'No Telefon',
            'Emel',
            'Status',
        ];
    }

    public function array(): array
    {
        return [
            [
                'G-2026-001',
                'Cikgu Ahmad bin Ismail',
                '850101-01-0001',
                '0123456789',
                'cikgu.ahmad@contoh.com',
                'Aktif',
            ],
        ];
    }
}
