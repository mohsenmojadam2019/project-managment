<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class ProjectAdminRoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::query()->firstOrCreate(
            ['name' => 'project_admin'],
            [
                'display_name' => 'Project Admin',
                'description' => 'Project admin is allowed to manage all the projects in a company.',
            ]
        );
    }
}
