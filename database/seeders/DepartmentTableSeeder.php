<?php

namespace Database\Seeders;

use App\Models\Designation;
use App\Models\LeaveType;
use App\Models\Team;
use Illuminate\Database\Seeder;

class DepartmentTableSeeder extends Seeder
{
    /**
     * Seed company departments and designations without duplicating them.
     */
    public function run($companyId): void
    {
        $departments = [
            'Marketing',
            'Sales',
            'Human Resources',
            'Public Relations',
            'Research',
            'Finance',
        ];

        $designations = [
            'Trainee',
            'Senior',
            'Junior',
            'Team Lead',
            'Project Manager',
        ];

        foreach ($departments as $department) {
            Team::query()->firstOrCreate([
                'team_name' => $department,
                'company_id' => $companyId,
            ]);
        }

        foreach ($designations as $designation) {
            Designation::query()->firstOrCreate([
                'name' => $designation,
                'company_id' => $companyId,
            ]);
        }

        $teamIds = Team::query()
            ->where('company_id', $companyId)
            ->pluck('id')
            ->values()
            ->all();

        $designationIds = Designation::query()
            ->where('company_id', $companyId)
            ->pluck('id')
            ->values()
            ->all();

        LeaveType::query()
            ->where('company_id', $companyId)
            ->update([
                'department' => json_encode($teamIds),
                'designation' => json_encode($designationIds),
            ]);
    }
}
