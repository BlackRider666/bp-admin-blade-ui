<?php

declare(strict_types=1);

namespace BlackParadise\LaravelAdminBladeUI\Tests\Fixtures;

use BlackParadise\CoreAdmin\Domain\Fields\HasManyField;
use BlackParadise\CoreAdmin\Domain\Fields\TextField;
use BlackParadise\LaravelAdmin\EntityDefinition;

/**
 * Host definition with a hasMany-embedded relation ('histories'). Drives the
 * create-form repeater rendering for ADMIN-V3-BUGS #1.
 */
final class StubJournalDefinition extends EntityDefinition
{
    public string $model = StubModel::class;

    public function resolveName(): string
    {
        return 'stub_journal';
    }

    public function fields(): array
    {
        return [
            TextField::make('issn_print'),
            HasManyField::make('histories', StubModel::class)
                ->withRelationName('historyItems')
                ->embed(StubJournalHistoryDefinition::class)
                ->owns()
                ->strategy('merge'),
        ];
    }
}
