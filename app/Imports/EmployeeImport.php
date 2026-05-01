<?php

namespace App\Imports;

use App\Models\Employee;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Carbon;

class EmployeeImport implements ToModel, WithHeadingRow
{
    protected $companyId;

    public function __construct($companyId)
    {
        $this->companyId = $companyId;
    }

    public function model(array $row)
    {
        if (empty($row['name']) || empty($row['nik'])) {
            return null;
        }

        // Parse join date safely
        $joinDate = null;
        try {
            if (!empty($row['join_date'])) {
                // If it's an excel serial date
                if (is_numeric($row['join_date'])) {
                    $joinDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['join_date'])->format('Y-m-d');
                } else {
                    $joinDate = Carbon::parse($row['join_date'])->format('Y-m-d');
                }
            }
        } catch (\Exception $e) {
            $joinDate = null;
        }

        $employee = new Employee([
            'company_id'  => $this->companyId,
            'name'        => $row['name'],
            'nik'         => $row['nik'],
            'npwp'        => $row['npwp_tin'] ?? null,
            'position'    => $row['position'] ?? null,
            'ptkp_status' => $row['ptkp_status'] ?? 'TK/0',
            'join_date'   => $joinDate,
        ]);
        
        // Let the model boot event or manual assign handle the ter_category if needed.
        // Actually, employee model boot event doesn't set ter_category automatically.
        // I need to set it based on ptkp_status.
        $employee->ter_category = Employee::determineTerCategory($employee->ptkp_status);
        
        return $employee;
    }
}
