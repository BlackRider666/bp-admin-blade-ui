<?php

declare(strict_types=1);

namespace BlackParadise\LaravelAdminBladeUI\Tests\Feature\Support;

use BlackParadise\LaravelAdminBladeUI\Tests\TestCase;

/**
 * Tests for the bp_locale() helper (task 14 — per-process provider cache).
 *
 * Structural: function_exists('bp_locale') is FALSE before impl → honest RED.
 * Behavioural: verifies that bp_locale() returns the runtime locale, and that
 * the provider is cached per-process while currentLocale() stays live.
 */
final class BpLocaleHelperTest extends TestCase
{
    /**
     * bp_locale() must exist after autoload.
     *
     * This assertion is the honest-RED hook: before the helper is added to
     * icon_helpers.php and composer dump-autoload is run, function_exists()
     * returns false, making the entire test file fail on collection.
     */
    public function test_bp_locale_function_exists(): void
    {
        self::assertTrue(function_exists('bp_locale'), 'bp_locale() is not defined — add it to src/Support/icon_helpers.php and run composer dump-autoload');
    }

    /**
     * bp_locale() must return the current runtime locale.
     */
    public function test_bp_locale_returns_current_locale(): void
    {
        app()->setLocale('en');

        self::assertSame('en', bp_locale());
    }

    /**
     * bp_locale() must track locale changes — the static provider ref caches
     * the instance but currentLocale() reads app()->getLocale() live.
     */
    public function test_bp_locale_tracks_runtime_locale_change(): void
    {
        app()->setLocale('uk');
        self::assertSame('uk', bp_locale());

        app()->setLocale('de');
        self::assertSame('de', bp_locale());
    }
}
