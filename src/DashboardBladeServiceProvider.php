<?php

namespace BlackParadise\AdminBladeUI;

use Illuminate\Support\ServiceProvider;

class DashboardBladeServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'bpadmin');
        $this->registerPublishes();
    }

    /**
     * Register
     */
    public function register()
    {

    }

    protected function registerPublishes()
    {
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/bpadmin'),
            __DIR__ . '/../public' => public_path('/'),
            __DIR__ . '/../resources/js' => resource_path('js/vendor/bpadmin'),
        ], 'bpadmin::ui-all');

        $this->publishes([
            __DIR__ . '/../public' => public_path('/'),
        ], 'bpadmin::ui-min');
    }
}
