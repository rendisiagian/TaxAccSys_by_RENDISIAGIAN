<?php

namespace App\Exports;

use App\Models\Vendor;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class VendorExport implements FromCollection, WithHeadings, WithMapping
{
    protected $companyId;

    public function __construct($companyId)
    {
        $this->companyId = $companyId;
    }

    public function collection()
    {
        return Vendor::where('company_id', $this->companyId)->get();
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

    public function map($vendor): array
    {
        return [
            $vendor->name,
            $vendor->tin,
            $vendor->email,
            $vendor->phone,
            $vendor->address,
            $vendor->contact_person,
        ];
    }
}
