<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class EmployeeTemplateExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        return [
            [
                'John Doe',
                '3171234567890001',
                '12.345.678.9-012.000',
                'Manager',
                'K/1',
                '2024-01-15'
            ]
        ];
    }

    public function headings(): array
    {
        return [
            'Name',
            'NIK',
            'NPWP (TIN)',
            'Position',
            'PTKP Status',
            'Join Date'
        ];
    }
}
