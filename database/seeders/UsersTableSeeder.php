<?php

namespace Database\Seeders;

use App\Enums\MaritalStatus;
use App\Models\ClientDetails;
use App\Models\Designation;
use App\Models\EmployeeDetails;
use App\Models\Role;
use App\Models\Team;
use App\Models\UniversalSearch;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class UsersTableSeeder extends Seeder
{
    /**
     * Seed deterministic demo users for one company.
     *
     * Re-running this seeder updates the same identities instead of adding
     * duplicate users, details, role pivots or universal-search rows.
     */
    public function run($companyId): void
    {
        $companyId = (int) $companyId;
        $count = max(0, (int) config('app.seed_record_count', 10));

        $adminRole = $this->role('admin', $companyId);
        $employeeRole = $this->role('employee', $companyId);
        $clientRole = $this->role('client', $companyId);

        $adminEmail = $companyId === 1 ? 'admin@example.com' : 'admin' . $companyId . '@example.com';
        $employeeEmail = $companyId === 1 ? 'employee@example.com' : 'employee' . $companyId . '@example.com';
        $clientEmail = $companyId === 1 ? 'client@example.com' : 'client' . $companyId . '@example.com';

        $admin = $this->demoUser($companyId, $adminEmail, 'Demo Admin');
        $this->addEmployeeDetails($admin, $employeeRole, $companyId);
        $admin->roles()->syncWithoutDetaching([$adminRole->id]);

        $employee = $this->demoUser($companyId, $employeeEmail, 'Demo Employee');
        $this->addEmployeeDetails($employee, $employeeRole, $companyId);

        $client = $this->demoUser($companyId, $clientEmail, 'Demo Client');
        $this->addClientDetails($client, $clientRole, $companyId);

        for ($index = 1; $index <= $count; $index++) {
            $client = $this->demoUser(
                $companyId,
                sprintf('client-%d-%d@example.test', $companyId, $index),
                'Demo Client ' . $index
            );
            $this->addClientDetails($client, $clientRole, $companyId);

            $employee = $this->demoUser(
                $companyId,
                sprintf('employee-%d-%d@example.test', $companyId, $index),
                'Demo Employee ' . $index
            );
            $this->addEmployeeDetails($employee, $employeeRole, $companyId);
        }
    }

    private function role(string $name, int $companyId): Role
    {
        $role = Role::query()
            ->where('name', $name)
            ->where('company_id', $companyId)
            ->first();

        if (! $role) {
            throw new RuntimeException("Required role [{$name}] is missing for company [{$companyId}].");
        }

        return $role;
    }

    private function demoUser(int $companyId, string $email, string $fallbackName): User
    {
        $user = User::query()->firstOrNew([
            'company_id' => $companyId,
            'email' => $email,
        ]);

        if (! $user->exists) {
            $user->name = $fallbackName;
            $user->password = Hash::make($this->demoPassword());
        }

        $user->gender = $user->gender ?: 'male';
        $user->rtl = 1;
        $user->locale = 'fa';
        $user->save();

        return $user;
    }

    private function demoPassword(): string
    {
        return (string) env('DEMO_USER_PASSWORD', 'Demo-ChangeMe-123!');
    }

    private function addEmployeeDetails(User $user, Role $employeeRole, int $companyId): void
    {
        $departmentId = Team::query()
            ->where('company_id', $companyId)
            ->inRandomOrder()
            ->value('id');

        $designationId = Designation::query()
            ->where('company_id', $companyId)
            ->inRandomOrder()
            ->value('id');

        if (! $departmentId || ! $designationId) {
            throw new RuntimeException("Departments/designations must be seeded before users for company [{$companyId}].");
        }

        $employee = EmployeeDetails::query()->firstOrNew([
            'user_id' => $user->id,
            'company_id' => $companyId,
        ]);

        if (! $employee->exists) {
            $employee->employee_id = 'EMP-' . $user->id;
            $employee->address = fake()->address;
            $employee->about_me = 'Demo employee profile';
            $employee->hourly_rate = fake()->numberBetween(15, 100);
            $employee->joining_date = now()->subMonths(9)->toDateTimeString();
            $employee->calendar_view = 'task,events,holiday,tickets,leaves,follow_ups';
            $employee->marital_status = MaritalStatus::Single;
        }

        $validDepartment = Team::query()
            ->where('company_id', $companyId)
            ->whereKey($employee->department_id)
            ->exists();

        $validDesignation = Designation::query()
            ->where('company_id', $companyId)
            ->whereKey($employee->designation_id)
            ->exists();

        if (! $validDepartment) {
            $employee->department_id = $departmentId;
        }

        if (! $validDesignation) {
            $employee->designation_id = $designationId;
        }

        $employee->save();

        UniversalSearch::query()->updateOrCreate([
            'searchable_id' => $user->id,
            'company_id' => $companyId,
            'module_type' => 'employee',
        ], [
            'title' => $user->name,
            'route_name' => 'employees.show',
        ]);

        $user->roles()->syncWithoutDetaching([$employeeRole->id]);
    }

    private function addClientDetails(User $user, Role $clientRole, int $companyId): void
    {
        UniversalSearch::query()->updateOrCreate([
            'searchable_id' => $user->id,
            'company_id' => $companyId,
            'module_type' => 'client',
        ], [
            'title' => $user->name,
            'route_name' => 'clients.show',
        ]);

        ClientDetails::query()->firstOrCreate([
            'user_id' => $user->id,
            'company_id' => $companyId,
        ], [
            'company_name' => fake()->company,
            'address' => fake()->address,
            'website' => 'https://example.test',
        ]);

        $user->roles()->syncWithoutDetaching([$clientRole->id]);
    }
}
