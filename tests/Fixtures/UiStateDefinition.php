<?php

declare(strict_types=1);

namespace BlackParadise\LaravelAdminBladeUI\Tests\Fixtures;

use BlackParadise\CoreAdmin\Domain\Contracts\Action\ActionContract;
use BlackParadise\CoreAdmin\Domain\Fields\TextField;
use BlackParadise\CoreAdmin\Domain\ValueObjects\EntityCapabilities;
use BlackParadise\LaravelAdmin\EntityDefinition;

/**
 * Configurable definition for EntityUiState / controls / actions rendering tests.
 * Entity name: 'ui_thing'. Not registered anywhere — passed to the presenter directly.
 */
final class UiStateDefinition extends EntityDefinition
{
    /**
     * @param list<ActionContract|class-string<ActionContract>> $actions
     */
    public function __construct(
        private readonly EntityCapabilities $caps = new EntityCapabilities(true, true, true),
        private readonly array $actions = [],
        bool $modelLess = false,
    ) {
        if (!$modelLess) {
            $this->model = StubModel::class;
        }
    }

    public function resolveName(): string
    {
        return 'ui_thing';
    }

    public function capabilities(): EntityCapabilities
    {
        return $this->caps;
    }

    /**
     * Spec 08: actions() returns class-strings. Configurable UiStateAction doubles are registered as
     * container instances under a synthetic id so the container-based resolver can build them;
     * real class-strings pass through untouched.
     *
     * @return list<class-string<ActionContract>>
     */
    public function actions(): array
    {
        $ids = [];
        foreach ($this->actions as $action) {
            if (is_string($action)) {
                $ids[] = $action;
                continue;
            }
            $id = 'ui-state-action.' . spl_object_id($action);
            app()->instance($id, $action);
            $ids[] = $id;
        }

        return $ids;
    }

    public function fields(): array
    {
        return [TextField::make('title')];
    }
}
