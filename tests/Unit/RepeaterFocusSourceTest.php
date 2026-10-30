<?php

declare(strict_types=1);

namespace BlackParadise\LaravelAdminBladeUI\Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * A16 / A17: source-content guard on resources/js/bpadmin.js (the shipped bundle is minified and
 * renames locals, so it cannot be grepped). bpRepeater.add() must focus the new row inside $nextTick
 * (after Alpine initialised the cloned relation select) and must not call Alpine.initTree manually
 * (that would initialise the select twice).
 */
final class RepeaterFocusSourceTest extends TestCase
{
    private function repeaterSource(): string
    {
        $js = file_get_contents(__DIR__ . '/../../resources/js/bpadmin.js');
        self::assertIsString($js);

        $start = strpos($js, "A.data('bpRepeater'");
        self::assertIsInt($start, 'bpRepeater component must exist in bpadmin.js');

        $end = strpos($js, "A.data('", $start + 10);

        return $end === false ? substr($js, $start) : substr($js, $start, $end - $start);
    }

    public function test_repeater_add_defers_focus_into_next_tick(): void
    {
        $src = $this->repeaterSource();

        self::assertMatchesRegularExpression('/\$nextTick\s*\(\s*(\(\s*\)|function\s*\(\s*\))?\s*(=>)?\s*\{?[^)]*focus\s*\(/s', $src);
    }

    public function test_repeater_add_does_not_focus_synchronously_before_next_tick(): void
    {
        $src = $this->repeaterSource();

        $focusPos = strpos($src, '.focus(');
        $tickPos  = strpos($src, '$nextTick');
        self::assertIsInt($tickPos, 'bpRepeater.add must use $nextTick');
        self::assertIsInt($focusPos);
        self::assertGreaterThan($tickPos, $focusPos, 'focus() must come after (inside) $nextTick');
    }

    public function test_repeater_does_not_call_alpine_init_tree(): void
    {
        $src = $this->repeaterSource();

        self::assertStringNotContainsString('initTree', $src);
    }
}
