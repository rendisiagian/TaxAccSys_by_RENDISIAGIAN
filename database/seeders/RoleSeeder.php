<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Manager',
                'slug' => 'manager',
                'description' => 'Full access — approve, edit, delete, and manage all data',
                'permissions' => [
                    'companies.manage', 'users.manage',
                    'journal.create', 'journal.edit', 'journal.delete', 'journal.approve',
                    'tax.create', 'tax.edit', 'tax.delete', 'tax.approve',
                    'coa.create', 'coa.edit', 'coa.delete',
                    'reconciliation.manage', 'export.manage',
                    'reports.view', 'audit.view', 'settings.manage',
                ],
            ],
            [
                'name' => 'Supervisor',
                'slug' => 'supervisor',
                'description' => 'Review, verify, and approve data submitted by Admin',
                'permissions' => [
                    'journal.create', 'journal.edit', 'journal.approve',
                    'tax.create', 'tax.edit', 'tax.approve',
                    'coa.create', 'coa.edit',
                    'reconciliation.manage', 'export.manage',
                    'reports.view', 'audit.view',
                ],
            ],
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'description' => 'Input and manage transactional data',
                'permissions' => [
                    'journal.create', 'journal.edit',
                    'tax.create', 'tax.edit',
                    'coa.create', 'coa.edit',
                    'export.manage',
                    'reports.view',
                ],
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['slug' => $role['slug']],
                $role
            );
        }
    }
}
