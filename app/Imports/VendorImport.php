<?php

namespace App\Imports;

use App\Models\Vendor;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class VendorImport implements ToModel, WithHeadingRow
{
    protected $companyId;

    public function __construct($companyId)
    {
        $this->companyId = $companyId;
    }

    public function model(array $row)
    {
        if (empty($row['name'])) {
            return null;
        }

        return new Vendor([
            'company_id'     => $this->companyId,
            'name'           => $row['name'],
            'tin'            => $row['npwp_tin'] ?? null,
            'email'          => $row['email'] ?? null,
            'phone'          => $row['phone'] ?? null,
            'address'        => $row['address'] ?? null,
            'contact_person' => $row['contact_person'] ?? null,
        ]);
    }
}
