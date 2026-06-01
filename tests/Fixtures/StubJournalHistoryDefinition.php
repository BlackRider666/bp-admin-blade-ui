<?php

declare(strict_types=1);

namespace BlackParadise\LaravelAdminBladeUI\Tests\Fixtures;

use BlackParadise\CoreAdmin\Domain\Fields\TextField;
use BlackParadise\LaravelAdmin\EntityDefinition;

/**
 * Embedded (child) definition used as the hasMany-embed target in
 * {@see StubJournalDefinition}. Exercises the create-form repeater rendering.
 */
final class StubJournalHistoryDefinition extends EntityDefinition
{
    public string $model = StubModel::class;

    public function resolveName(): string
    {
        return 'stub_journal_history';
    }

    public function fields(): array
    {
        return [
            TextField::make('title')->required(),
            TextField::make('period'),
        ];
    }
}
