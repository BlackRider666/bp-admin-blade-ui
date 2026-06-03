<?php

declare(strict_types=1);

namespace BlackParadise\LaravelAdminBladeUI\Tests\Fixtures;

use BlackParadise\CoreAdmin\Domain\Fields\TextField;
use BlackParadise\LaravelAdmin\EntityDefinition;

/**
 * EntityDefinition stub whose entity name is supplied at construction time.
 *
 * Unlike {@see StubEntityDefinition} (fixed 'stub_entity'), this lets a single
 * test register several distinct entities — needed to exercise the config-driven
 * sidebar menu, which resolves entities by name from the registry.
 */
final class StubNamedDefinition extends EntityDefinition
{
    public string $model = StubModel::class;

    public function __construct(private readonly string $entityName) {}

    public function resolveName(): string
    {
        return $this->entityName;
    }

    public function fields(): array
    {
        return [
            TextField::make('name'),
        ];
    }
}
