<?php

declare(strict_types=1);

namespace BlackParadise\LaravelAdminBladeUI\Tests\Fixtures;

use BlackParadise\CoreAdmin\Domain\Fields\EditorField;
use BlackParadise\CoreAdmin\Domain\Fields\TextField;
use BlackParadise\CoreAdmin\Domain\Fields\TranslatableField;
use BlackParadise\LaravelAdmin\EntityDefinition;

/**
 * Test-only EntityDefinition that contains BOTH a standalone EditorField
 * AND a TranslatableField with innerType='editor'.
 *
 * Used by QuillConditionalLoadTest to verify that Quill assets are loaded
 * exactly ONCE even when both component types are present on the same page.
 * This catches the bare-@once cross-component dedup bug (FINDING 1).
 */
final class StubMixedEditorDefinition extends EntityDefinition
{
    public string $model = StubModel::class;

    public function resolveName(): string
    {
        return 'stub_mixed_editor_entity';
    }

    public function fields(): array
    {
        return [
            TextField::make('title'),
            EditorField::make('body'),
            TranslatableField::make('content')->asEditor(),
        ];
    }
}
