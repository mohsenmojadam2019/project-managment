<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run($companyId): void
    {
        $role = Role::query()->firstOrCreate(
            [
                'name' => 'Manager',
                'company_id' => $companyId,
            ],
            [
                'display_name' => 'Manager',
            ]
        );

        $permissions = Permission::query()->get();

        // sync is idempotent and also removes stale permission relations.
        $role->perms()->sync($permissions->pluck('id')->all());
    }
}
