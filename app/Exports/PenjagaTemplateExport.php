<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PenjagaTemplateExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return [
            'Nama',
            'No KP',
            'No Telefon',
            'Emel',
            'Alamat',
            'Hubungan',
        ];
    }

    public function array(): array
    {
        return [
            [
                'Siti Aminah binti Yusof',
                '800101-01-0002',
                '0123456789',
                'siti.aminah@contoh.com',
                'No 1, Jalan Contoh, 43000 Kajang',
                'Ibu',
            ],
        ];
    }
}
