<?php

declare(strict_types=1);

namespace BlackParadise\LaravelAdminBladeUI\Tests\Feature;

use BlackParadise\LaravelAdminBladeUI\Tests\TestCase;

/**
 * RED test for Task A / Change #10: bpRelativeTime double-init guard.
 *
 * Problem: bpRelativeTime.init() always creates a NEW arrow this._tick and adds
 * it to bpTimeTicker. A second init() call without an intervening destroy()
 * (Livewire / Turbo re-init) leaks a second subscriber into bpTimeTicker.subs.
 *
 * Fix: make init() idempotent — remove the previous tick before re-subscribing:
 *   init() { if (this._tick) bpTimeTicker.remove(this._tick); this.refresh(); ... }
 *
 * This is a *source-content characterisation test*: it reads resources/js/bpadmin.js
 * (NOT the built bundle — esbuild --minify renames locals, making bundle-grep unreliable).
 *
 * Before the edit: the guard substring is absent from source → honest RED.
 * After the edit:  the guard is present → GREEN (no npm build required for test to pass).
 */
final class RelativeTimeDoubleInitGuardTest extends TestCase
{
    public function test_source_init_guards_against_double_subscription(): void
    {
        $source = (string) file_get_contents(
            __DIR__ . '/../../resources/js/bpadmin.js'
        );

        // The guard must appear inside init(), not only in destroy().
        // We verify that init() itself starts with the idempotency guard.
        // Expected form: init() { if (this._tick) bpTimeTicker.remove(this._tick); ...
        self::assertStringContainsString(
            'init() { if (this._tick) bpTimeTicker.remove(this._tick)',
            $source,
            'bpRelativeTime.init() must begin with the double-init guard ' .
            '"if (this._tick) bpTimeTicker.remove(this._tick)"; ' .
            'guard absent from init() in resources/js/bpadmin.js.',
        );
    }
}
