<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Scopes\CompanyScope;
use Illuminate\Database\Seeder;

class ProjectAdminRoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::withoutGlobalScope(CompanyScope::class)->updateOrCreate(
            ['name' => 'project-admin'],
            [
                'display_name' => 'Project Admin',
                'description' => 'Project admin is allowed to manage all the projects in a company.',
            ]
        );
    }
}
