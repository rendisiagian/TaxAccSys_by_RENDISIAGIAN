<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class VendorTemplateExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        return [
            [
                'PT Contoh Supplier Hebat',
                '02.345.678.9-012.000',
                'sales@contoh-supplier.com',
                '021-9876543',
                'Jl. Gatot Subroto Kav. 2, Jakarta',
                'Ibu Siti'
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
