<?php

namespace Tests\Feature;

use Tests\TestCase;

class QualityRegressionTest extends TestCase
{
    public function test_dashboard_and_date_filter_routes_require_authentication(): void
    {
        $this->get('/account/dashboard')->assertRedirect();
        $this->get('/account/dashboard-advanced?tab=finance')->assertRedirect();
        $this->post('/account/dashboard/week-timelog', ['date' => '2026-08-31'])->assertRedirect();
        $this->get('/account/dashboard/private_calendar?start=2026-08-01&end=2026-08-31')->assertRedirect();
    }

    public function test_invoice_mutations_require_authentication_and_safe_http_methods(): void
    {
        $this->post('/account/invoices/delete-image', ['id' => 1])->assertRedirect();
        $this->post('/account/invoices/update-status/1')->assertRedirect();

        $routes = file_get_contents(base_path('routes/web.php'));
        $this->assertStringNotContainsString("Route::get('invoices/delete-image'", $routes);
        $this->assertStringNotContainsString("Route::get('invoices/update-status/{invoiceID}'", $routes);
        $this->assertStringContainsString("Route::post('invoices/delete-image'", $routes);
        $this->assertStringContainsString("Route::post('invoices/update-status/{invoiceID}'", $routes);
    }

    public function test_core_role_and_permission_routes_require_authentication(): void
    {
        $this->post('/account/user-permissions/customPermissions/1')->assertRedirect();
        $this->post('/account/user-permissions/resetPermissions/1')->assertRedirect();
        $this->get('/account/employees')->assertRedirect();
    }

    public function test_dashboard_permission_fallbacks_are_not_swapped(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/DashboardController.php'));

        $this->assertMatchesRegularExpression(
            "/view_finance_dashboard'\\] == 4\\).*?activeTab = \\$tab \\?: 'finance';.*?financeDashboard\\(\\);/s",
            $controller
        );
        $this->assertMatchesRegularExpression(
            "/view_ticket_dashboard'\\] == 4\\).*?activeTab = \\$tab \\?: 'ticket';.*?ticketDashboard\\(\\);/s",
            $controller
        );
    }
}
