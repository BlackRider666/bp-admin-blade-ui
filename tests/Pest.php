<?php

declare(strict_types=1);

/*
 * Pest bootstrap for bp-laravel-admin-blade-ui.
 *
 * Architecture tests live under tests/Architecture/ (no app boot).
 * Feature/Integration tests use BlackParadise\LaravelAdminBladeUI\Tests\TestCase
 * which boots Orchestra Testbench with DashboardServiceProvider and
 * BladeUIServiceProvider registered.
 */

use BlackParadise\LaravelAdminBladeUI\Tests\TestCase;

uses(TestCase::class)->in(__DIR__ . '/Feature', __DIR__ . '/Integration');
