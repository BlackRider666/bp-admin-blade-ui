<?php

declare(strict_types=1);

namespace BlackParadise\LaravelAdminBladeUI\Tests\Fixtures;

use BlackParadise\CoreAdmin\Domain\Fields\TextField;
use BlackParadise\LaravelAdmin\EntityDefinition;

/**
 * Minimal EntityDefinition for Blade presenter tests.
 *
 * Resolves to the entity name 'stub_entity' (overridden) with two
 * plain text fields. Has no associated Eloquent model that needs
 * registration — the presenter tests do not exercise persistence.
 */
final class StubEntityDefinition extends EntityDefinition
{
    public string $model = StubModel::class;

    public function resolveName(): string
    {
        return 'stub_entity';
    }

    public function fields(): array
    {
        return [
            TextField::make('name'),
            TextField::make('email'),
        ];
    }
}
