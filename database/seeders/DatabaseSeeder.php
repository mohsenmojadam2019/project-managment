<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed application reference data and demo/company data safely.
     *
     * APP_KEY is deliberately never generated here. Rotating APP_KEY during
     * db:seed invalidates encrypted application data, cookies and sessions.
     */
    public function run(): void
    {
        config(['app.seeding' => true]);

        try {
            $this->call([
                CountriesTableSeeder::class,
                SmtpSettingsSeeder::class,
                EmailSettingSeeder::class,
                CoreDatabaseSeeder::class,
                ModulePermissionSeeder::class,
                OrganisationSettingsTableSeeder::class,
            ]);

            Company::query()->select('id')->orderBy('id')->chunkById(100, function ($companies): void {
                foreach ($companies as $company) {
                    $this->seedCompany((int) $company->id);
                }
            });

            if (!App::environment('codecanyon')) {
                Artisan::call('sync-user-permissions all');
            }
        } finally {
            config(['app.seeding' => false]);
            cache()->flush();
        }
    }

    /**
     * Seed all records scoped to a company.
     */
    private function seedCompany(int $companyId): void
    {
        if (!App::environment('codecanyon')) {
            $companySeeders = [
                DepartmentTableSeeder::class,
                UsersTableSeeder::class,
                BankAccountSeeder::class,
                ProjectCategorySeeder::class,
                ProjectSeeder::class,
                EstimateSeeder::class,
                ExpenseSeeder::class,
                TicketSeeder::class,
                TicketSettingSeeder::class,
                RoleSeeder::class,
                LeaveSeeder::class,
                NoticesTableSeeder::class,
                EventTableSeeder::class,
                LeadSeeder::class,
                TaxTableSeeder::class,
                ProductTableSeeder::class,
                ContractTypeTableSeeder::class,
                ContractTableSeeder::class,
                LeadsTableSeeder::class,
                MessageSeeder::class,
                ShiftSeeder::class,
                AttendanceTableSeeder::class,
                AppreciationSeeder::class,
            ];

            foreach ($companySeeders as $seeder) {
                $this->call($seeder, false, ['companyId' => $companyId]);
            }
        }

        $this->call(EmployeePermissionSeeder::class, false, ['companyId' => $companyId]);
    }
}
