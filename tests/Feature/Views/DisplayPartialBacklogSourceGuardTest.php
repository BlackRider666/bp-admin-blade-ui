<?php

declare(strict_types=1);

namespace BlackParadise\LaravelAdminBladeUI\Tests\Feature\Views;

use PHPUnit\Framework\TestCase;

/**
 * Source-guard for the display-partial backlog cleanup (M3/M4/M5).
 *
 * These three assertions are genuinely RED pre-IMPL:
 *
 *  M3/S3 — text.blade.php must be removed (byte-identical duplicate of _default.blade.php;
 *           @includeFirst falls through to _default after deletion — identical output).
 *
 *  M4/S3 — translatable.blade.php must use the injected $bpLocale variable rather than
 *           calling bp_locale() directly (locale resolved once in field-display.blade.php
 *           and passed down as a prop).
 *
 *  M5/S3 — field-display.blade.php must not contain the stale "relation cases below"
 *           comment (the phrase no longer describes the scope of $bpLocale after the
 *           relation-partial restructure).
 *
 * RED pre-IMPL: all three conditions hold in the current source → assertions FAIL.
 * Green post-IMPL: IMPL agent deletes text.blade.php, replaces bp_locale() with $bpLocale
 * in translatable.blade.php, and rewrites the stale comment → assertions PASS permanently.
 */
final class DisplayPartialBacklogSourceGuardTest extends TestCase
{
    private string $fieldDisplayDir;

    private string $fieldDisplayComponent;

    protected function setUp(): void
    {
        parent::setUp();

        $packageRoot = dirname(__DIR__, 3);

        $this->fieldDisplayDir = $packageRoot . '/resources/views/components/field-display';
        $this->fieldDisplayComponent = $packageRoot . '/resources/views/components/field-display.blade.php';
    }

    // ---------- M3/S3 — text.blade.php must be removed ----------

    /**
     * M3/S3: text.blade.php is byte-identical to _default.blade.php.
     * TextField::type() returns 'text', so @includeFirst resolves to _default.blade.php
     * once the duplicate is deleted — output is unchanged.
     * Pre-IMPL: file still exists → assertion FAILS (RED).
     */
    public function test_m3_text_partial_is_removed(): void
    {
        self::assertFalse(
            file_exists($this->fieldDisplayDir . '/text.blade.php'),
            'field-display/text.blade.php must be deleted — it is byte-identical to _default.blade.php. '
            . '@includeFirst falls through to _default after removal, so rendered output is unchanged. '
            . 'IMPL agent must delete this file.',
        );
    }

    // ---------- M4/S3 — translatable.blade.php must not call bp_locale() ----------

    /**
     * M4/S3: translatable.blade.php must use the injected $bpLocale prop (resolved
     * once per component render in field-display.blade.php) rather than calling
     * bp_locale() itself.
     * Pre-IMPL: '$defaultLocale = bp_locale();' is present → assertion FAILS (RED).
     */
    public function test_m4_translatable_partial_does_not_call_bp_locale(): void
    {
        $path = $this->fieldDisplayDir . '/translatable.blade.php';

        self::assertFileExists($path, 'translatable.blade.php must exist (it is NOT the file to delete).');

        $content = file_get_contents($path);

        self::assertIsString($content, 'translatable.blade.php must be readable.');
        self::assertStringNotContainsString(
            'bp_locale(',
            $content,
            'translatable.blade.php must not call bp_locale() directly. '
            . 'The locale is already resolved in field-display.blade.php and injected as $bpLocale. '
            . 'IMPL agent must replace "$defaultLocale = bp_locale();" with "$defaultLocale = $bpLocale;".',
        );
    }

    // ---------- M5/S3 — field-display.blade.php stale comment must be removed ----------

    /**
     * M5/S3: the comment "relation cases below" in field-display.blade.php is stale —
     * it no longer describes the scope of $bpLocale after the relation-partial restructure.
     * Pre-IMPL: the comment is still present → assertion FAILS (RED).
     */
    public function test_m5_field_display_component_does_not_contain_stale_relation_cases_comment(): void
    {
        self::assertFileExists(
            $this->fieldDisplayComponent,
            'field-display.blade.php must exist.',
        );

        $content = file_get_contents($this->fieldDisplayComponent);

        self::assertIsString($content, 'field-display.blade.php must be readable.');
        self::assertStringNotContainsString(
            'relation cases below',
            $content,
            'field-display.blade.php must not contain the stale "relation cases below" comment. '
            . 'IMPL agent must rewrite the comment to reflect the actual scope of $bpLocale.',
        );
    }
}
