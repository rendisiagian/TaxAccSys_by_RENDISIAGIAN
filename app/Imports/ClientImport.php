<?php

namespace App\Imports;

use App\Models\Client;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ClientImport implements ToModel, WithHeadingRow
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

        return new Client([
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
