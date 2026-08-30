<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Seed the company manager role and keep its permissions in sync.
     */
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

        if ($role->display_name !== 'Manager') {
            $role->display_name = 'Manager';
            $role->save();
        }

        $role->perms()->sync(Permission::query()->pluck('id')->all());
    }
}
