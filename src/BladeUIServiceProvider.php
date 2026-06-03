<?php

declare(strict_types=1);

namespace BlackParadise\LaravelAdminBladeUI;

use BlackParadise\LaravelAdmin\Http\Presenters\AuthPresenterInterface;
use BlackParadise\LaravelAdmin\Http\Presenters\DashboardPresenterInterface;
use BlackParadise\LaravelAdmin\Http\Presenters\EntityPresenterInterface;
use BlackParadise\LaravelAdminBladeUI\Http\Presenters\BladeAuthPresenter;
use BlackParadise\LaravelAdminBladeUI\Http\Presenters\BladeDashboardPresenter;
use BlackParadise\LaravelAdminBladeUI\Http\Presenters\BladeEntityPresenter;
use Illuminate\Support\ServiceProvider;

final class BladeUIServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Load UI helpers (bp_icon, ...). Also wired via composer "files"
        // autoload for fresh installs; the require_once + function_exists guard
        // keeps it available even when a consumer's autoloader metadata is stale
        // (e.g. local path-repo installs that predate this helper file).
        require_once __DIR__ . '/Support/icon_helpers.php';

        // Override default JSON presenters with Blade-specific implementations.
        // Routes remain exclusively in bp-laravel-admin.
        $this->app->bind(EntityPresenterInterface::class, BladeEntityPresenter::class);
        $this->app->bind(AuthPresenterInterface::class, BladeAuthPresenter::class);
        $this->app->bind(DashboardPresenterInterface::class, BladeDashboardPresenter::class);
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'bpadmin');

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/bpadmin'),
        ], 'bpadmin-views');

        $this->publishes([
            __DIR__ . '/../public/bpadmin' => public_path('vendor/bpadmin'),
        ], 'bpadmin-assets');
    }
}
