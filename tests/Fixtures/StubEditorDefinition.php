<?php

declare(strict_types=1);

namespace BlackParadise\LaravelAdminBladeUI\Tests\Fixtures;

use BlackParadise\CoreAdmin\Domain\Fields\EditorField;
use BlackParadise\CoreAdmin\Domain\Fields\TextField;
use BlackParadise\LaravelAdmin\EntityDefinition;

/**
 * Test-only EntityDefinition that contains an EditorField.
 * Used exclusively by QuillConditionalLoadTest to verify Quill
 * assets are injected via @stack('bp-head') only on form pages.
 */
final class StubEditorDefinition extends EntityDefinition
{
    public string $model = StubModel::class;

    public function resolveName(): string
    {
        return 'stub_editor_entity';
    }

    public function fields(): array
    {
        return [
            TextField::make('title'),
            EditorField::make('body'),
        ];
    }
}
