<?php

declare(strict_types=1);

namespace BlackParadise\LaravelAdminBladeUI\Tests;

use BlackParadise\LaravelAdmin\DashboardServiceProvider;
use BlackParadise\LaravelAdminBladeUI\BladeUIServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ViewErrorBag;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

/**
 * Base test case for Blade UI feature/integration tests.
 *
 * Bootstraps Laravel via Orchestra Testbench and registers both
 * the upstream DashboardServiceProvider (routes, default bindings)
 * and the BladeUIServiceProvider (Blade-specific presenter overrides
 * and view registration).
 */
abstract class TestCase extends OrchestraTestCase
{
    /**
     * {@inheritDoc}
     */
    protected function getPackageProviders($app): array
    {
        return [
            DashboardServiceProvider::class,
            BladeUIServiceProvider::class,
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:' . base64_encode(random_bytes(32)));

        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        // Disable entity auto-discovery so tests control registration explicitly.
        $app['config']->set('bpadmin.discovery_paths', []);
    }

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Mirror Illuminate\View\Middleware\ShareErrorsFromSession: views always
        // expect $errors to be a ViewErrorBag. Direct presenter calls bypass
        // the web middleware stack, so share an empty bag here.
        View::share('errors', new ViewErrorBag());

        // Inline schema: a single shared 'tags' table backs the BelongsTo
        // relation-options coverage test. Each Orchestra test runs against a
        // fresh in-memory SQLite, so no teardown / RefreshDatabase needed.
        Schema::create('tags', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
        });
    }
}
