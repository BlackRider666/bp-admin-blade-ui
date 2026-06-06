<?php

declare(strict_types=1);

namespace BlackParadise\LaravelAdminBladeUI\Tests\Feature;

use BlackParadise\LaravelAdminBladeUI\Tests\TestCase;

/**
 * RED tests for A27–A30: file-content assertions (no Blade render needed).
 *
 * These tests assert post-fix state of static files. They are RED now
 * because the bugs still exist.
 *
 * - A27: bpadmin-tailwind-plugin.cjs content glob points to admin-blade-ui (not laravel-admin-blade-ui-next)
 *        and package.json name is admin-blade-ui.
 * - A28: bpadmin-tailwind-plugin.cjs exports theme.extend with animation/keyframes/boxShadow/backgroundImage/bp-* colors.
 * - A29: resources/views/components/flash.blade.php does NOT exist (deleted).
 * - A30: resources/js/bpadmin.js does NOT contain bulkDelete( method on bpBulkSelect.
 */
final class BugFixFileContentTest extends TestCase
{
    private string $packageRoot;

    protected function setUp(): void
    {
        parent::setUp();

        // Resolve the package root from the known autoload path.
        $this->packageRoot = realpath(__DIR__ . '/../../') ?: __DIR__ . '/../../';
    }

    // ------------------------------------------------------------------
    // A27 (V4): tailwind plugin content glob + package.json name
    // ------------------------------------------------------------------

    /**
     * A27: bpadmin-tailwind-plugin.cjs content glob must point to the existing
     * vendor path 'admin-blade-ui', NOT the stale 'laravel-admin-blade-ui-next'.
     * Currently points to laravel-admin-blade-ui-next — RED.
     */
    public function test_a27_tailwind_plugin_content_glob_points_to_correct_package_path(): void
    {
        $pluginFile = $this->packageRoot . '/bpadmin-tailwind-plugin.cjs';

        self::assertFileExists($pluginFile, 'bpadmin-tailwind-plugin.cjs must exist');

        $content = file_get_contents($pluginFile);

        // Post-fix: glob uses the correct package name.
        self::assertStringContainsString(
            'vendor/black-paradise/admin-blade-ui/',
            $content,
            'content glob must reference admin-blade-ui (the actual Packagist package name)',
        );

        // Bug: stale package name that does not exist on disk.
        self::assertStringNotContainsString(
            'laravel-admin-blade-ui-next',
            $content,
            'content glob must NOT reference the stale laravel-admin-blade-ui-next path',
        );
    }

    /**
     * A27: package.json name must be "admin-blade-ui" (aligned with composer.json
     * black-paradise/admin-blade-ui). Currently "laravel-admin-blade-ui-next" — RED.
     */
    public function test_a27_package_json_name_is_aligned_with_composer_package_name(): void
    {
        $packageJsonFile = $this->packageRoot . '/package.json';

        self::assertFileExists($packageJsonFile, 'package.json must exist');

        $content = file_get_contents($packageJsonFile);
        $data    = json_decode($content, true);

        self::assertIsArray($data, 'package.json must be valid JSON');
        self::assertArrayHasKey('name', $data, 'package.json must have a name field');

        // Post-fix: name aligned with composer black-paradise/admin-blade-ui.
        self::assertSame(
            'admin-blade-ui',
            $data['name'],
            'package.json name must be admin-blade-ui to align with Packagist name',
        );
    }

    // ------------------------------------------------------------------
    // A28 (V5): tailwind plugin exports theme.extend with design tokens
    // ------------------------------------------------------------------

    /**
     * A28: bpadmin-tailwind-plugin.cjs must export a `theme.extend` object
     * containing animation, keyframes, boxShadow, backgroundImage and bp-* colors
     * so host projects receive the tokens via require().
     * Currently the plugin has no theme key in its exports — RED.
     */
    public function test_a28_tailwind_plugin_exports_theme_extend_with_design_tokens(): void
    {
        $pluginFile = $this->packageRoot . '/bpadmin-tailwind-plugin.cjs';

        self::assertFileExists($pluginFile, 'bpadmin-tailwind-plugin.cjs must exist');

        $content = file_get_contents($pluginFile);

        // Post-fix: the plugin must export a theme (or theme.extend) object.
        self::assertStringContainsString(
            'theme',
            $content,
            'plugin must export a theme property',
        );

        // Post-fix: design-token categories present in the exported theme.
        self::assertStringContainsString('animation', $content, 'plugin theme must include animation tokens');
        self::assertStringContainsString('keyframes', $content, 'plugin theme must include keyframes tokens');
        self::assertStringContainsString('boxShadow', $content, 'plugin theme must include boxShadow tokens');
        self::assertStringContainsString('backgroundImage', $content, 'plugin theme must include backgroundImage tokens');

        // Post-fix: bp-* color tokens present.
        self::assertStringContainsString("'bp-primary'", $content, 'plugin theme must include bp-primary color');
        self::assertStringContainsString("'bp-surface'", $content, 'plugin theme must include bp-surface color');
    }

    // ------------------------------------------------------------------
    // A28b (V5 follow-up): tailwind plugin missing tokens found by adversarial review
    // ------------------------------------------------------------------

    /**
     * A28b: the adversarial review found that while the existing A28 test passes
     * (bp-primary/bp-surface/animation/keyframes/boxShadow/backgroundImage exist),
     * the plugin is still missing tokens that the views actively use:
     *
     *  - theme.extend.colors must include 'bp-gray' (used in locale tabs, labels, etc.)
     *  - theme.extend.colors must include 'bp-input-bg' (used in field inputs)
     *  - theme.extend must include 'fontFamily' (used by display/mono classes)
     *  - theme.extend must include 'backgroundSize' (used by .bp-grid grid-dots)
     *  - addBase must register --bpadmin-gray CSS variables (gray scale palette)
     *  - addBase must register --bpadmin-input-bg CSS variable (input background)
     *
     * Host projects do `theme: { extend: bpadmin.theme.extend }` — any token absent
     * from the plugin is absent from the host build, breaking those utility classes.
     *
     * Currently the plugin has none of these tokens — RED.
     */
    public function test_a28b_tailwind_plugin_includes_bp_gray_bp_input_bg_fontfamily_backgroundsize_tokens(): void
    {
        $pluginFile = $this->packageRoot . '/bpadmin-tailwind-plugin.cjs';

        self::assertFileExists($pluginFile, 'bpadmin-tailwind-plugin.cjs must exist');

        $content = file_get_contents($pluginFile);

        // Post-fix: 'bp-gray' color scale must be in the plugin's theme.extend.colors.
        self::assertStringContainsString(
            "'bp-gray'",
            $content,
            "plugin theme.extend.colors must include 'bp-gray' (used in text-bp-gray-* utilities in views)",
        );

        // Post-fix: 'bp-input-bg' color token must be in the plugin's theme.extend.colors.
        self::assertStringContainsString(
            "'bp-input-bg'",
            $content,
            "plugin theme.extend.colors must include 'bp-input-bg' (used in bg-bp-input-bg utilities in field inputs)",
        );

        // Post-fix: fontFamily must be in theme.extend (sans/display/mono used via .font-display etc.).
        self::assertStringContainsString(
            'fontFamily',
            $content,
            'plugin theme.extend must include fontFamily (font-display, font-mono-bp classes rely on it)',
        );

        // Post-fix: backgroundSize must be in theme.extend (bg-grid-16/bg-grid-24 used in .bp-grid variant).
        self::assertStringContainsString(
            'backgroundSize',
            $content,
            'plugin theme.extend must include backgroundSize (bg-grid-16/bg-grid-24 tokens)',
        );

        // Post-fix: addBase must register --bpadmin-gray-* CSS variables (gray scale palette).
        self::assertStringContainsString(
            '--bpadmin-gray',
            $content,
            'addBase must register --bpadmin-gray-* CSS variables (gray scale matches bp-gray-* tokens)',
        );

        // Post-fix: addBase must register --bpadmin-input-bg CSS variable.
        self::assertStringContainsString(
            '--bpadmin-input-bg',
            $content,
            'addBase must register --bpadmin-input-bg CSS variable (referenced by bp-input-bg token)',
        );
    }

    // ------------------------------------------------------------------
    // A29 (V6): flash.blade.php does NOT exist (dead-code removal)
    // ------------------------------------------------------------------

    /**
     * A29: resources/views/components/flash.blade.php must be deleted.
     * Currently the file exists — RED.
     */
    public function test_a29_flash_blade_component_does_not_exist(): void
    {
        $flashFile = $this->packageRoot . '/resources/views/components/flash.blade.php';

        // Post-fix: the dead-code file is removed.
        self::assertFileDoesNotExist(
            $flashFile,
            'flash.blade.php must be deleted — toasts.blade.php is the canonical flash component',
        );
    }

    // ------------------------------------------------------------------
    // A30 (V7): bpBulkSelect has no bulkDelete() method (dead-code removal)
    // ------------------------------------------------------------------

    /**
     * A30: resources/js/bpadmin.js must NOT contain a bulkDelete( method
     * on bpBulkSelect. Currently the method exists — RED.
     */
    public function test_a30_bpadmin_js_does_not_contain_bulk_delete_method(): void
    {
        $jsFile = $this->packageRoot . '/resources/js/bpadmin.js';

        self::assertFileExists($jsFile, 'resources/js/bpadmin.js must exist');

        $content = file_get_contents($jsFile);

        // Post-fix: bulkDelete() removed — native confirm() UX anti-pattern gone.
        self::assertStringNotContainsString(
            'bulkDelete(',
            $content,
            'bpadmin.js must NOT contain bulkDelete() — it is dead code replaced by $store.confirm.ask',
        );
    }
}
