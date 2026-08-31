<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class RepositoryHardeningTest extends TestCase
{
    private string $root;

    protected function setUp(): void
    {
        parent::setUp();
        $this->root = dirname(__DIR__, 2);
    }

    public function test_state_changing_routes_do_not_accept_get(): void
    {
        $routes = file_get_contents($this->root . '/routes/web.php');
        $uris = [
            'attendances/update-clock-in',
            'products/empty-cart',
            'invoices/delete-image',
            'invoices/payment-reminder/{invoiceID}',
            'invoices/update-status/{invoiceID}',
            'invoices/toggle-shipping-address/{invoice}',
            'estimates/delete-image',
            'estimates/change-status/{id}',
            'proposals/delete-image',
            'proposals-template/delete-image',
            'gdpr/lead/approve-reject/{id}/{type}',
            'gdpr/customer/approve-reject/{id}/{type}',
            'hide-webhook-url',
            'estimates-template/delete-image',
        ];

        foreach ($uris as $uri) {
            $this->assertStringNotContainsString("Route::get('{$uri}'", $routes, $uri);
            $this->assertStringContainsString("Route::post('{$uri}'", $routes, $uri);
        }
    }

    public function test_csrf_exemptions_are_provider_scoped(): void
    {
        $middleware = file_get_contents($this->root . '/app/Http/Middleware/VerifyCsrfToken.php');

        foreach (["'*-webhook/*'", "'*_webhook/*'", "'*_webhook'", "'*-webhook'"] as $broad) {
            $this->assertStringNotContainsString($broad, $middleware);
        }

        foreach (['paystack', 'flutterwave', 'mollie', 'payfast', 'square', 'razorpay', 'paypal'] as $provider) {
            $this->assertStringContainsString("'{$provider}-webhook/*'", $middleware);
        }
        $this->assertStringContainsString("'verify-webhook/*'", $middleware);
    }

    public function test_direct_runtime_dependencies_are_reproducible(): void
    {
        $composer = json_decode(file_get_contents($this->root . '/composer.json'), true, 512, JSON_THROW_ON_ERROR);
        $allowedVirtual = ['psr/http-factory-implementation'];

        foreach (['require', 'require-dev'] as $section) {
            foreach ($composer[$section] as $package => $constraint) {
                if (in_array($package, $allowedVirtual, true) || str_starts_with($package, 'ext-') || $package === 'php') {
                    continue;
                }
                $this->assertNotSame('*', $constraint, "Wildcard constraint: {$package}");
                if (str_starts_with($constraint, 'dev-')) {
                    $this->assertMatchesRegularExpression('/#[a-f0-9]{40}$/', $constraint, "Unpinned dev dependency: {$package}");
                }
            }
        }

        $this->assertSame('stable', $composer['minimum-stability']);
    }

    public function test_device_schema_is_versioned_and_scratch_sql_is_removed(): void
    {
        $this->assertFileExists($this->root . '/database/migrations/2026_08_31_041500_normalize_device_tracking_schema.php');
        $this->assertFileDoesNotExist($this->root . '/w');
    }
}
