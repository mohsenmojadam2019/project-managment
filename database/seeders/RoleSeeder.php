<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Scopes\CompanyScope;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run($companyId): void
    {
        $role = Role::withoutGlobalScope(CompanyScope::class)
            ->where('name', 'manager')
            ->where('company_id', $companyId)
            ->first();

        if (!$role) {
            $role = new Role();
            $role->forceFill([
                'name' => 'manager',
                'display_name' => 'Manager',
                'company_id' => $companyId,
            ]);
            $role->save();
        } elseif ($role->display_name !== 'Manager') {
            $role->forceFill(['display_name' => 'Manager'])->save();
        }

        $permissions = Permission::query()->pluck('id')->all();

        // sync is idempotent and also removes stale permission relations.
        $role->perms()->sync($permissions);
    }
}
