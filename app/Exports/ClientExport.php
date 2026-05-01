<?php

namespace App\Exports;

use App\Models\Client;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ClientExport implements FromCollection, WithHeadings, WithMapping
{
    protected $companyId;

    public function __construct($companyId)
    {
        $this->companyId = $companyId;
    }

    public function collection()
    {
        return Client::where('company_id', $this->companyId)->get();
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

    public function map($client): array
    {
        return [
            $client->name,
            $client->tin,
            $client->email,
            $client->phone,
            $client->address,
            $client->contact_person,
        ];
    }
}
