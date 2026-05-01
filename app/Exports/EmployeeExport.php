<?php

namespace App\Exports;

use App\Models\Employee;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class EmployeeExport implements FromCollection, WithHeadings, WithMapping
{
    protected $companyId;

    public function __construct($companyId)
    {
        $this->companyId = $companyId;
    }

    public function collection()
    {
        return Employee::where('company_id', $this->companyId)->get();
    }

    public function headings(): array
    {
        return [
            'Name',
            'NIK (ID Card)',
            'NPWP (TIN)',
            'Position',
            'PTKP Status',
            'TER Category',
            'Join Date'
        ];
    }

    public function map($employee): array
    {
        return [
            $employee->name,
            $employee->nik,
            $employee->npwp,
            $employee->position,
            $employee->ptkp_status,
            $employee->ter_category,
            $employee->join_date,
        ];
    }
}
