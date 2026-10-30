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

    /**
     * No function-static provider cache: a provider rebound in the container
     * (Octane request boundary / test swap) must be honoured on the next call.
     */
    public function test_bp_locale_resolves_provider_from_container_on_every_call(): void
    {
        $make = static fn(string $locale): \BlackParadise\CoreAdmin\Domain\Contracts\LocaleProviderContract => new class ($locale) implements \BlackParadise\CoreAdmin\Domain\Contracts\LocaleProviderContract {
            public function __construct(private readonly string $locale) {}

            public function availableLocales(): array
            {
                return [$this->locale];
            }

            public function defaultLocale(): string
            {
                return $this->locale;
            }

            public function currentLocale(): string
            {
                return $this->locale;
            }
        };

        app()->instance(\BlackParadise\CoreAdmin\Domain\Contracts\LocaleProviderContract::class, $make('aa'));
        self::assertSame('aa', bp_locale());

        app()->instance(\BlackParadise\CoreAdmin\Domain\Contracts\LocaleProviderContract::class, $make('bb'));
        self::assertSame('bb', bp_locale());
    }
}
