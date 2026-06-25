<?php

declare(strict_types=1);

namespace BlackParadise\LaravelAdminBladeUI\Tests\Feature\Support;

use BlackParadise\LaravelAdminBladeUI\Support\IconSet;
use BlackParadise\LaravelAdminBladeUI\Tests\TestCase;
use ReflectionProperty;

/**
 * Task 18 — IconSet::map() static memoization contract.
 *
 * Verifies that map() stores its result in a private static $cachedMap property
 * and returns the same cached content on repeated calls.
 *
 * Before implementation: ReflectionProperty(IconSet::class, 'cachedMap') throws
 * ReflectionException (property does not exist) → honest RED.
 * After implementation: property exists and is populated → GREEN.
 */
final class IconSetMemoizationTest extends TestCase
{
    public function test_map_is_memoized_in_a_static_cache(): void
    {
        $prop = new ReflectionProperty(IconSet::class, 'cachedMap');
        $prop->setAccessible(true);

        IconSet::map();

        self::assertIsArray($prop->getValue());
        self::assertNotEmpty($prop->getValue());

        // identity: a second call returns the SAME cached array instance content
        self::assertSame(IconSet::map(), IconSet::map());
    }

    public function test_map_still_contains_known_icon_keys_after_memoization(): void
    {
        self::assertArrayHasKey('list', IconSet::map());
        self::assertArrayHasKey('cog', IconSet::map());
        self::assertArrayHasKey('dashboard', IconSet::map());
    }
}
