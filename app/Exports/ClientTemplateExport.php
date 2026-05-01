<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ClientTemplateExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        return [
            [
                'PT Contoh Klien Abadi',
                '01.234.567.8-901.000',
                'info@contoh.com',
                '021-1234567',
                'Jl. Jendral Sudirman Kav. 1, Jakarta',
                'Bapak Budi'
            ]
        ];
    }

    public function headings(): array
    {
        return [
            'Name',
            'NPWP (TIN)',
            'Email',
            'Phone',
            'Address',
            'Contact Person'
        ];
    }
}
