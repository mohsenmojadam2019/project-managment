<?php

namespace App\Providers;

use App\Models\Company;
use Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider;
use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Cashier;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\Config;
use App\Models\SmsSetting;
use App\Models\PaymentGatewayCredentials;

class AppServiceProvider extends ServiceProvider
{

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Cashier 16+ no longer exposes ignoreMigrations(); older supported
        // versions did. Keep compatibility without crashing package discovery.
        if (method_exists(Cashier::class, 'ignoreMigrations')) {
            Cashier::ignoreMigrations();
        }

        Sanctum::ignoreMigrations();

        if ($this->app->isLocal()) {
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
        }

        if (config('app.redirect_https')) {
            $this->app['request']->server->set('HTTPS', true);
        }
    }

    public function boot()
    {
        $this->loadMigrationsFrom(base_path('Modules/UniversalBundle/Modules'));

        Cashier::useCustomerModel(Company::class);

        if (config('app.redirect_https')) {
            URL::forceScheme('https');
        }

        Schema::defaultStringLength(191);

        if (app()->environment('development')) {
            $this->app->register(IdeHelperServiceProvider::class);
        }

        CarbonInterval::macro('formatHuman', function ($totalMinutes, $seconds = false): string {

            if ($seconds) {
                return static::seconds($totalMinutes)->cascade()->forHumans(['short' => true, 'options' => 0]);
                /** @phpstan-ignore-line */
            }

            return static::minutes($totalMinutes)->cascade()->forHumans(['short' => true, 'options' => 0]);
            /** @phpstan-ignore-line */
        });

        // Model::preventLazyLoading(app()->environment('development'));
    }

}
