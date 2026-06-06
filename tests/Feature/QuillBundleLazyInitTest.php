<?php

declare(strict_types=1);

namespace BlackParadise\LaravelAdminBladeUI\Tests\Feature;

use BlackParadise\LaravelAdminBladeUI\Tests\TestCase;

/**
 * RED test for bug #3 / A18+A19: Quill lazy-init in hidden containers.
 *
 * bpQuill must NOT boot Quill immediately in a hidden container (inactive locale
 * tabs, collapsed repeater rows). Instead it must defer initialisation until the
 * container is visible (offsetParent !== null) and re-attempt via
 * IntersectionObserver when it becomes visible.
 *
 * This test is a *bundle-content characterisation test*: it reads the shipped
 * public/bpadmin/bpadmin.js and checks for the presence of the lazy-init guard
 * token `offsetParent`. The token is absent from the current bundle → RED.
 * After editing resources/js/bpadmin.js and running `npm run build:all` the
 * rebuilt bundle will contain the token → GREEN.
 */
final class QuillBundleLazyInitTest extends TestCase
{
    public function test_shipped_bundle_lazy_inits_quill_in_hidden_containers(): void
    {
        $bundle = (string) file_get_contents(
            __DIR__ . '/../../public/bpadmin/bpadmin.js'
        );

        // Lazy-init guard token — present only after the source change is rebuilt.
        self::assertStringContainsString(
            'offsetParent',
            $bundle,
            'bpQuill must defer init until visible; run `npm run build:all` after editing resources/js.',
        );
    }
}
