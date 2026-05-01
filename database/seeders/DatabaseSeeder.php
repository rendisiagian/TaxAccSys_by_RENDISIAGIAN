<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Company;
use App\Models\FiscalYear;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Roles
        $this->call(RoleSeeder::class);

        // 2. Create sample companies
        $company1 = Company::create([
            'name' => 'PT Maju Sejahtera',
            'npwp' => '0123456789012000',
            'nitku' => '0123456789012000000000',
            'address' => 'Jl. Sudirman No. 1',
            'city' => 'Jakarta Selatan',
            'province' => 'DKI Jakarta',
            'postal_code' => '12190',
            'phone' => '021-5551234',
            'email' => 'info@majusejahtera.co.id',
            'business_type' => 'Trading & Services',
            'klu_code' => '46100',
            'tax_office' => 'KPP Pratama Jakarta Setiabudi',
            'company_type' => 'regular',
        ]);

        $company2 = Company::create([
            'name' => 'PT Bangun Karya Indonesia',
            'npwp' => '9876543210098000',
            'nitku' => '9876543210098000000000',
            'address' => 'Jl. Gatot Subroto No. 15',
            'city' => 'Jakarta Pusat',
            'province' => 'DKI Jakarta',
            'postal_code' => '10270',
            'phone' => '021-5559876',
            'email' => 'info@bangunkarya.co.id',
            'business_type' => 'Konstruksi',
            'klu_code' => '41011',
            'tax_office' => 'KPP Pratama Jakarta Tanah Abang',
            'company_type' => 'construction',
        ]);

        // 3. Create branches for company 1
        Branch::create([
            'company_id' => $company1->id,
            'name' => 'Kantor Pusat Jakarta',
            'code' => 'JKT',
            'nitku' => '0123456789012000000000',
            'address' => 'Jl. Sudirman No. 1',
            'city' => 'Jakarta Selatan',
            'is_head_office' => true,
        ]);

        Branch::create([
            'company_id' => $company1->id,
            'name' => 'Cabang Surabaya',
            'code' => 'SBY',
            'nitku' => '0123456789012000000001',
            'address' => 'Jl. Pemuda No. 10',
            'city' => 'Surabaya',
        ]);

        // 4. Create branches & projects for construction company
        Branch::create([
            'company_id' => $company2->id,
            'name' => 'Head Office',
            'code' => 'HO',
            'nitku' => '9876543210098000000000',
            'address' => 'Jl. Gatot Subroto No. 15',
            'city' => 'Jakarta Pusat',
            'is_head_office' => true,
        ]);

        Project::create([
            'company_id' => $company2->id,
            'name' => 'Proyek Gedung Perkantoran BSD',
            'code' => 'PRJ-001',
            'description' => 'Pembangunan gedung perkantoran 10 lantai',
            'location' => 'BSD City, Tangerang',
            'client_name' => 'PT Sinar Mas Land',
            'contract_number' => 'KON/2026/001',
            'contract_value' => 50000000000,
            'start_date' => '2026-01-01',
            'end_date' => '2027-12-31',
            'status' => 'active',
        ]);

        Project::create([
            'company_id' => $company2->id,
            'name' => 'Proyek Jalan Tol Semarang',
            'code' => 'PRJ-002',
            'description' => 'Pembangunan ruas jalan tol 5 km',
            'location' => 'Semarang, Jawa Tengah',
            'client_name' => 'PT Jasa Marga',
            'contract_number' => 'KON/2026/002',
            'contract_value' => 120000000000,
            'start_date' => '2026-03-01',
            'end_date' => '2028-06-30',
            'status' => 'active',
        ]);

        // 5. Create fiscal years
        foreach ([$company1, $company2] as $company) {
            FiscalYear::create([
                'company_id' => $company->id,
                'name' => 'FY 2026',
                'start_date' => '2026-01-01',
                'end_date' => '2026-12-31',
                'status' => 'open',
                'is_current' => true,
            ]);
        }

        // 6. Create users
        $managerRole = Role::where('slug', 'manager')->first();
        $supervisorRole = Role::where('slug', 'supervisor')->first();
        $adminRole = Role::where('slug', 'admin')->first();

        $manager = User::create([
            'name' => 'Tax Manager',
            'email' => 'manager@taxsystem.test',
            'password' => bcrypt('password'),
            'role_id' => $managerRole->id,
            'current_company_id' => $company1->id,
            'locale' => 'id',
        ]);
        $manager->companies()->attach([$company1->id, $company2->id]);

        $supervisor = User::create([
            'name' => 'Tax Supervisor',
            'email' => 'supervisor@taxsystem.test',
            'password' => bcrypt('password'),
            'role_id' => $supervisorRole->id,
            'current_company_id' => $company1->id,
            'locale' => 'id',
        ]);
        $supervisor->companies()->attach([$company1->id, $company2->id]);

        $admin = User::create([
            'name' => 'Tax Admin',
            'email' => 'admin@taxsystem.test',
            'password' => bcrypt('password'),
            'role_id' => $adminRole->id,
            'current_company_id' => $company1->id,
            'locale' => 'id',
        ]);
        $admin->companies()->attach([$company1->id]);

        // 7. Seed Chart of Accounts for both companies
        $this->call(ChartOfAccountSeeder::class);

        // Also seed COA for company 2
        $this->command->info('Seeding COA for company 2...');
        $originalCompany = Company::first();
        $coaRecords = \App\Models\ChartOfAccount::where('company_id', $originalCompany->id)->orderBy('sort_order')->get();

        $codeToId = [];
        foreach ($coaRecords as $coa) {
            $parentId = null;
            if ($coa->parent_id) {
                $parentCode = \App\Models\ChartOfAccount::find($coa->parent_id)?->account_code;
                $parentId = $codeToId[$parentCode] ?? null;
            }

            $newCoa = \App\Models\ChartOfAccount::create([
                'company_id'     => $company2->id,
                'parent_id'      => $parentId,
                'account_code'   => $coa->account_code,
                'account_name'   => $coa->account_name,
                'account_type'   => $coa->account_type,
                'normal_balance' => $coa->normal_balance,
                'is_header'      => $coa->is_header,
                'is_active'      => true,
                'is_system'      => true,
                'level'          => $coa->level,
                'sort_order'     => $coa->sort_order,
            ]);
            $codeToId[$coa->account_code] = $newCoa->id;
        }
    }
}
