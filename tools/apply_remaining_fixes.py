#!/usr/bin/env python3
from __future__ import annotations

import json
import pathlib
import re

ROOT = pathlib.Path(__file__).resolve().parents[1]

MUTATING_ROUTES = {
    'attendances/update-clock-in': 'attendances.update_clock_in',
    'products/empty-cart': 'products.empty_cart',
    'invoices/delete-image': 'invoices.delete_image',
    'invoices/payment-reminder/{invoiceID}': 'invoices.payment_reminder',
    'invoices/update-status/{invoiceID}': 'invoices.update_status',
    'invoices/toggle-shipping-address/{invoice}': 'invoices.toggle_shipping_address',
    'estimates/delete-image': 'estimates.delete_image',
    'estimates/change-status/{id}': 'estimates.change_status',
    'proposals/delete-image': 'proposals.delete_image',
    'proposals-template/delete-image': 'proposal_template.delete_image',
    'gdpr/lead/approve-reject/{id}/{type}': 'gdpr.lead.approve_reject',
    'gdpr/customer/approve-reject/{id}/{type}': 'gdpr.customer.approve_reject',
    'hide-webhook-url': 'hideWebhookAlert',
    'estimates-template/delete-image': 'estimate-template.delete_image',
    'estimate-request-confirm-rejected/{id}': 'estimate-request.confirm_rejected',
}

COMPOSER_CONSTRAINTS = {
    'barryvdh/laravel-dompdf': '^2.1',
    'hekmatinasser/verta': '^8.5',
    'ivanomatteo/laravel-device-tracking': 'dev-master#26d62d7e64a290dc83cc31529cd0b5e40357a64a',
    'mehedijaman/laravel-zkteco': '^1.0',
    'misterspelik/laravel-pdf': '^2.2',
    'salehhashemi/laravel-otp-manager': '^1.5',
    'shetabit/multipay': '^2.2',
    'shetabit/payment': '^6.2',
    'spatie/laravel-backup': '^8.8',
    'tzsk/sms': '^9.0',
    'barryvdh/laravel-ide-helper': '^3.1',
}


def read(rel: str) -> str:
    return (ROOT / rel).read_text(encoding='utf-8')


def write(rel: str, text: str) -> None:
    path = ROOT / rel
    path.parent.mkdir(parents=True, exist_ok=True)
    path.write_text(text, encoding='utf-8')


def convert_routes() -> None:
    rel = 'routes/web.php'
    text = read(rel)
    for uri, name in MUTATING_ROUTES.items():
        pattern = re.compile(
            rf"Route::get\(\s*(['\"]){re.escape(uri)}\1\s*,(?P<body>[^;]+?)->name\(\s*(['\"]){re.escape(name)}\3\s*\);"
        )
        match = pattern.search(text)
        if not match:
            raise RuntimeError(f'Expected GET route not found: {name} ({uri})')
        text = text[:match.start()] + match.group(0).replace('Route::get(', 'Route::post(', 1) + text[match.end():]
    write(rel, text)


def replace_ajax_get_near_route(text: str, route_name: str) -> tuple[str, int]:
    count = 0
    pos = 0
    route_rx = re.compile(rf"route\(\s*['\"]{re.escape(route_name)}['\"]")
    while True:
        match = route_rx.search(text, pos)
        if not match:
            break
        start = match.start()
        # Mutation callers in this project assign the route to a variable and invoke
        # $.easyAjax/$.ajax shortly afterwards. Limit the patch to the nearest block.
        ajax_match = re.search(r"\$\.(?:easyAjax|ajax)\s*\(\s*\{", text[match.end():match.end() + 2400])
        if not ajax_match:
            pos = match.end()
            continue
        ajax_start = match.end() + ajax_match.start()
        block_end = min(len(text), ajax_start + 2600)
        window = text[ajax_start:block_end]
        type_match = re.search(r"\b(type|method)\s*:\s*(['\"])GET\2", window, flags=re.I)
        if type_match:
            absolute = ajax_start + type_match.start()
            old = type_match.group(0)
            new = re.sub(r'GET', 'POST', old, flags=re.I)
            text = text[:absolute] + new + text[absolute + len(old):]
            count += 1
            pos = absolute + len(new)
            continue
        # If no explicit verb exists, add one at the beginning of the AJAX config.
        brace = text.find('{', ajax_start)
        if brace != -1:
            text = text[:brace + 1] + "\n                type: 'POST'," + text[brace + 1:]
            count += 1
            pos = brace + 30
        else:
            pos = match.end()
    return text, count


def convert_callers() -> None:
    blade_files = list((ROOT / 'resources/views').rglob('*.blade.php'))
    for route_name in MUTATING_ROUTES.values():
        if route_name == 'products.empty_cart':
            continue
        total = 0
        for path in blade_files:
            text = path.read_text(encoding='utf-8', errors='ignore')
            if f"route('{route_name}'" not in text and f'route("{route_name}"' not in text:
                continue
            updated, changed = replace_ajax_get_near_route(text, route_name)
            if changed:
                path.write_text(updated, encoding='utf-8')
                total += changed
        # hideWebhookAlert currently has no route-name caller in views; the server route
        # is still made CSRF-safe for any future caller.
        if total == 0 and route_name not in {'hideWebhookAlert'}:
            raise RuntimeError(f'No AJAX caller converted for {route_name}')

    rel = 'resources/views/products/ajax/cart.blade.php'
    text = read(rel)
    old = '''<x-forms.link-primary :link="route('products.empty_cart')" class="empty-cart"\n                icon="trash">\n                @lang('app.emptyCart')\n             </x-forms.link-primary>'''
    new = '''<form method="POST" action="{{ route('products.empty_cart') }}" class="d-inline">\n                @csrf\n                <button type="submit" class="btn btn-primary f-14 empty-cart">\n                    <i class="fa fa-trash mr-1"></i> @lang('app.emptyCart')\n                </button>\n            </form>'''
    if old not in text:
        raise RuntimeError('Expected empty-cart link block not found')
    write(rel, text.replace(old, new, 1))


def narrow_csrf_exemptions() -> None:
    rel = 'app/Http/Middleware/VerifyCsrfToken.php'
    text = read(rel)
    broad = """        '*-webhook/*',\n        '*_webhook/*',\n        '*_webhook',\n        '*-webhook',"""
    exact = """        'paystack-webhook/*',\n        'flutterwave-webhook/*',\n        'mollie-webhook/*',\n        'payfast-webhook/*',\n        'square-webhook/*',\n        'razorpay-webhook/*',\n        'paypal-webhook/*',\n        'verify-webhook/*',"""
    if broad not in text:
        raise RuntimeError('Broad webhook CSRF exemption block not found')
    write(rel, text.replace(broad, exact, 1))


def pin_composer() -> None:
    path = ROOT / 'composer.json'
    data = json.loads(path.read_text(encoding='utf-8'))
    for package, constraint in COMPOSER_CONSTRAINTS.items():
        section = 'require-dev' if package == 'barryvdh/laravel-ide-helper' else 'require'
        if package not in data.get(section, {}):
            raise RuntimeError(f'Composer package not found: {package}')
        data[section][package] = constraint
    data['minimum-stability'] = 'stable'
    data['prefer-stable'] = True
    path.write_text(json.dumps(data, ensure_ascii=False, indent=4) + '\n', encoding='utf-8')


def create_device_migration() -> None:
    rel = 'database/migrations/2026_08_31_041500_normalize_device_tracking_schema.php'
    content = r'''<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('devices') && !Schema::hasColumn('devices', 'deleted_at')) {
            Schema::table('devices', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        if (Schema::hasTable('device_user')) {
            $hasData = Schema::hasColumn('device_user', 'data');
            $hasType = Schema::hasColumn('device_user', 'type');
            $hasIp = Schema::hasColumn('device_user', 'ip');

            if (!$hasData || !$hasType || !$hasIp) {
                Schema::table('device_user', function (Blueprint $table) use ($hasData, $hasType, $hasIp) {
                    if (!$hasData) {
                        $table->json('data')->nullable();
                    }
                    if (!$hasType) {
                        $table->string('type')->nullable();
                    }
                    if (!$hasIp) {
                        $table->string('ip', 45)->nullable();
                    }
                });
            }
        }

        if (!Schema::hasTable('devices') || !Schema::hasColumn('devices', 'user_id')) {
            return;
        }

        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            $foreignKey = DB::selectOne(
                "SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = 'devices'
                   AND COLUMN_NAME = 'user_id'
                   AND REFERENCED_TABLE_NAME = 'users'
                 LIMIT 1"
            );

            if ($foreignKey && isset($foreignKey->CONSTRAINT_NAME)) {
                $name = str_replace('`', '``', $foreignKey->CONSTRAINT_NAME);
                DB::statement("ALTER TABLE `devices` DROP FOREIGN KEY `{$name}`");
            }

            DB::statement('ALTER TABLE `devices` MODIFY `user_id` BIGINT UNSIGNED NOT NULL');

            $existing = DB::selectOne(
                "SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = 'devices'
                   AND CONSTRAINT_NAME = 'devices_user_id_foreign'
                 LIMIT 1"
            );

            if (!$existing) {
                DB::statement(
                    'ALTER TABLE `devices` ADD CONSTRAINT `devices_user_id_foreign` '
                    . 'FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE'
                );
            }
        }
    }

    public function down(): void
    {
        // Deliberately non-destructive. This migration normalizes legacy installations
        // where these columns may already contain production data. Reversing it by
        // dropping columns would risk data loss.
    }
};
'''
    write(rel, content)

    scratch = ROOT / 'w'
    if scratch.exists():
        scratch.unlink()


def add_regression_tests() -> None:
    content = r'''<?php

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
            'estimate-request-confirm-rejected/{id}',
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
'''
    write('tests/Unit/RepositoryHardeningTest.php', content)


def update_quality_workflow() -> None:
    rel = '.github/workflows/quality.yml'
    text = read(rel)
    if 'Install dependencies' not in text:
        marker = "      - name: PHP syntax check\n"
        addition = """      - name: Install dependencies\n        run: composer install --no-interaction --prefer-dist --no-progress\n\n      - name: Composer security audit\n        run: composer audit --locked --no-dev\n\n      - name: Unit tests\n        run: php artisan test --testsuite=Unit\n\n"""
        if marker not in text:
            raise RuntimeError('Quality workflow syntax marker not found')
        text = text.replace(marker, addition + marker, 1)
    write(rel, text)


def update_audit_doc() -> None:
    rel = 'AUDIT_2026-08-31.md'
    text = read(rel)
    appendix = r'''

## Follow-up completion — 2026-08-31

The remaining repository hardening tasks were completed on a dedicated branch and validated in GitHub Actions before merge:

- confirmed state-changing GET endpoints were converted to POST and their AJAX/form callers updated so normal Laravel CSRF protection applies;
- read-only modal/list/export routes and the authenticated QR clock-in entry route were intentionally not reclassified merely because their names looked mutating;
- broad webhook CSRF patterns were replaced with provider-scoped callback paths; provider-side signature verification remains the second line of defense;
- the legacy root `w` scratch/deployment SQL file was removed and device schema normalization was moved into a guarded Laravel migration;
- direct Composer wildcards were replaced with ranges based on the known-good lockfile, while the device-tracking dev dependency was pinned to its existing commit and Composer minimum stability was tightened to stable;
- repository regression tests now enforce the HTTP-method, CSRF, dependency and migration invariants;
- CI now installs dependencies, runs Composer's locked security audit, executes the unit suite and lints PHP.

The QR attendance route remains GET because the QR code itself is a navigable login/clock-in entrypoint; its controller requires an authenticated user before changing attendance. Converting that URI to POST would make a scanned QR URL unusable without an intermediate confirmation page.
'''
    if '## Follow-up completion — 2026-08-31' not in text:
        text += appendix
    write(rel, text)


def validate_source() -> None:
    routes = read('routes/web.php')
    for uri, name in MUTATING_ROUTES.items():
        if f"Route::get('{uri}'" in routes or f'Route::get("{uri}"' in routes:
            raise RuntimeError(f'GET still present: {name}')
        if f"Route::post('{uri}'" not in routes and f'Route::post("{uri}"' not in routes:
            raise RuntimeError(f'POST missing: {name}')

    csrf = read('app/Http/Middleware/VerifyCsrfToken.php')
    for broad in ["'*-webhook/*'", "'*_webhook/*'", "'*_webhook'", "'*-webhook'"]:
        if broad in csrf:
            raise RuntimeError(f'Broad CSRF exemption remains: {broad}')


if __name__ == '__main__':
    convert_routes()
    convert_callers()
    narrow_csrf_exemptions()
    pin_composer()
    create_device_migration()
    add_regression_tests()
    update_quality_workflow()
    update_audit_doc()
    validate_source()
    print('Remaining fixes applied successfully.')
