<?php

declare(strict_types=1);

namespace BlackParadise\LaravelAdminBladeUI\Tests\Feature\Support;

use BlackParadise\LaravelAdminBladeUI\Support\IconSet;
use BlackParadise\LaravelAdminBladeUI\Tests\TestCase;

/**
 * Tests for the bp_icon() helper and its IconSet resolution order.
 */
final class IconHelperTest extends TestCase
{
    public function test_renders_a_bundled_icon_with_the_given_class(): void
    {
        $svg = bp_icon('book', 'h-5 w-5 text-red-500');

        self::assertStringStartsWith('<svg', $svg);
        self::assertStringContainsString('class="h-5 w-5 text-red-500"', $svg);
        self::assertStringContainsString('stroke="currentColor"', $svg);
        // The 'book' glyph's path data must be embedded.
        self::assertStringContainsString('M12 6.253v13', $svg);
    }

    public function test_unknown_name_falls_back_to_the_default_icon(): void
    {
        $fallback = bp_icon('does-not-exist');

        // The default icon ('list') path must be used.
        self::assertStringContainsString(IconSet::get(IconSet::DEFAULT), $fallback);
    }

    public function test_app_defined_icons_extend_the_set_via_config(): void
    {
        config()->set('bpadmin.icons', [
            'rocket' => '<path d="M5 5l14 14"/>',
        ]);

        $svg = bp_icon('rocket');

        self::assertStringContainsString('<path d="M5 5l14 14"/>', $svg);
        self::assertStringStartsWith('<svg', $svg);
    }

    public function test_a_full_svg_value_is_returned_verbatim(): void
    {
        $raw = '<svg viewBox="0 0 10 10"><circle cx="5" cy="5" r="4"/></svg>';

        // Passed directly...
        self::assertSame($raw, bp_icon($raw));

        // ...and via the config map.
        config()->set('bpadmin.icons', ['blob' => $raw]);
        self::assertSame($raw, bp_icon('blob'));
    }

    public function test_bundled_set_contains_every_icon_used_by_example_menu(): void
    {
        foreach (['users', 'book', 'comment'] as $name) {
            self::assertTrue(IconSet::has($name), "Icon '{$name}' missing from IconSet");
        }
    }
}
