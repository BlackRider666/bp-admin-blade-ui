<?php

declare(strict_types=1);

namespace BlackParadise\LaravelAdminBladeUI\Tests\Feature;

use BlackParadise\LaravelAdminBladeUI\Tests\TestCase;

/**
 * RED test for Task 16: bpRelativeTime — one shared bpTimeTicker instead of N setIntervals.
 *
 * Each bpRelativeTime component previously spawned its own setInterval(60000). This test
 * verifies that the refactor landed in source: a single shared bpTimeTicker (Set-based
 * subscriber list) is defined in register(A), and bpRelativeTime.init()/destroy() delegate
 * to bpTimeTicker.add / bpTimeTicker.remove instead of calling setInterval directly.
 *
 * This is a *source-content characterisation test*: it reads resources/js/bpadmin.js
 * (NOT the built bundle) so it goes GREEN immediately after the source edit, without
 * requiring `npm run build:all` (that happens in Task 19).
 *
 * Before the edit: 'bpTimeTicker' is absent from source → honest RED.
 * After the edit:  all three tokens present → GREEN.
 */
final class RelativeTimeSingleTickerTest extends TestCase
{
    public function test_source_defines_single_shared_ticker_for_relative_time(): void
    {
        $source = (string) file_get_contents(__DIR__ . '/../../resources/js/bpadmin.js');

        self::assertStringContainsString(
            'bpTimeTicker',
            $source,
            'bpTimeTicker must be defined in resources/js/bpadmin.js; shared ticker absent from source.',
        );

        self::assertStringContainsString(
            'bpTimeTicker.add(this._tick)',
            $source,
            'bpRelativeTime.init() must subscribe via bpTimeTicker.add(this._tick).',
        );

        self::assertStringContainsString(
            'bpTimeTicker.remove(this._tick)',
            $source,
            'bpRelativeTime.destroy() must unsubscribe via bpTimeTicker.remove(this._tick).',
        );
    }
}
