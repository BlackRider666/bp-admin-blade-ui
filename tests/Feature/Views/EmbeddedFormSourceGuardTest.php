<?php

declare(strict_types=1);

namespace BlackParadise\LaravelAdminBladeUI\Tests\Feature\Views;

use PHPUnit\Framework\TestCase;

/**
 * Source-guard for A7: create.blade.php and edit.blade.php must NOT contain:
 *  - the view-level host-FK skip (`$f->relationKind() === 'belongsTo'`), and
 *  - the dead fallback (`$embeddedDef->fields()`) that re-instantiates the
 *    embedded definition when meta()['embeddedFields'] is absent.
 *
 * After Task 2.2 (Core), BuildFormViewUseCase ALWAYS attaches meta()['embeddedFields']
 * (already filtered by visibleOnForm + !pointsBackTo(host)), so both code paths are
 * dead. Task 2.3 IMPL must delete them.
 *
 * RED pre-IMPL: patterns are present in the view files → assertions fail.
 * Green post-IMPL: patterns removed → assertions pass (permanent regression guard).
 *
 * A7 acceptance check: `grep -rn "relationKind() === 'belongsTo'" resources/views` → 0.
 */
final class EmbeddedFormSourceGuardTest extends TestCase
{
    private function createBladePath(): string
    {
        return dirname(__DIR__, 3) . '/resources/views/pages/create.blade.php';
    }

    private function editBladePath(): string
    {
        return dirname(__DIR__, 3) . '/resources/views/pages/edit.blade.php';
    }

    // ---------- RED tests (patterns present pre-IMPL → fail until IMPL removes them) ----------

    public function test_create_blade_does_not_contain_belongs_to_relation_kind_check(): void
    {
        $content = file_get_contents($this->createBladePath());

        self::assertIsString($content, 'create.blade.php must be readable.');
        self::assertStringNotContainsString(
            "relationKind() === 'belongsTo'",
            $content,
            'create.blade.php still contains the redundant view-level host-FK skip. '
            . "Task 2.3 IMPL must remove it — Core already filters the host-FK field in meta()['embeddedFields'].",
        );
    }

    public function test_edit_blade_does_not_contain_belongs_to_relation_kind_check(): void
    {
        $content = file_get_contents($this->editBladePath());

        self::assertIsString($content, 'edit.blade.php must be readable.');
        self::assertStringNotContainsString(
            "relationKind() === 'belongsTo'",
            $content,
            'edit.blade.php still contains the redundant view-level host-FK skip. '
            . "Task 2.3 IMPL must remove it — Core already filters the host-FK field in meta()['embeddedFields'].",
        );
    }

    public function test_create_blade_does_not_contain_embedded_def_fields_fallback(): void
    {
        $content = file_get_contents($this->createBladePath());

        self::assertIsString($content, 'create.blade.php must be readable.');
        self::assertStringNotContainsString(
            '$embeddedDef->fields()',
            $content,
            'create.blade.php still contains the dead fallback that re-instantiates the embedded definition. '
            . "Task 2.3 IMPL must remove this branch — meta()['embeddedFields'] is always present after Core 2.2.",
        );
    }

    public function test_edit_blade_does_not_contain_embedded_def_fields_fallback(): void
    {
        $content = file_get_contents($this->editBladePath());

        self::assertIsString($content, 'edit.blade.php must be readable.');
        self::assertStringNotContainsString(
            '$embeddedDef->fields()',
            $content,
            'edit.blade.php still contains the dead fallback that re-instantiates the embedded definition. '
            . "Task 2.3 IMPL must remove this branch — meta()['embeddedFields'] is always present after Core 2.2.",
        );
    }

    // ---------- Pre-green tests (positive guards — these pass before and after IMPL) ----------

    public function test_create_blade_reads_meta_embedded_fields_key(): void
    {
        $content = file_get_contents($this->createBladePath());

        self::assertIsString($content, 'create.blade.php must be readable.');
        self::assertStringContainsString(
            "meta()['embeddedFields']",
            $content,
            "create.blade.php must read meta()['embeddedFields'] — the Core-decorated sub-field list.",
        );
    }

    public function test_edit_blade_reads_meta_embedded_fields_key(): void
    {
        $content = file_get_contents($this->editBladePath());

        self::assertIsString($content, 'edit.blade.php must be readable.');
        self::assertStringContainsString(
            "meta()['embeddedFields']",
            $content,
            "edit.blade.php must read meta()['embeddedFields'] — the Core-decorated sub-field list.",
        );
    }
}
