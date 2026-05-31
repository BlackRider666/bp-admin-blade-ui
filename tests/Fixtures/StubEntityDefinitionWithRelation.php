<?php

declare(strict_types=1);

namespace BlackParadise\LaravelAdminBladeUI\Tests\Fixtures;

use BlackParadise\CoreAdmin\Domain\Fields\BelongsToField;
use BlackParadise\CoreAdmin\Domain\Fields\TextField;
use BlackParadise\LaravelAdmin\EntityDefinition;

/**
 * EntityDefinition fixture exercising a BelongsToField → Tag relation.
 *
 * Drives BladeEntityPresenter::populateRelationOptions() coverage:
 * the presenter's create/edit hooks must query Tag::all() and decorate
 * the field's meta with {id, label} options.
 */
final class StubEntityDefinitionWithRelation extends EntityDefinition
{
    public string $model = StubModel::class;

    public function resolveName(): string
    {
        return 'stub_with_relation';
    }

    public function fields(): array
    {
        return [
            TextField::make('name'),
            BelongsToField::make('tag_id', Tag::class)
                ->withRelationName('tag')
                ->withDisplayField('name'),
        ];
    }
}
