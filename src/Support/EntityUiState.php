<?php

declare(strict_types=1);

namespace BlackParadise\LaravelAdminBladeUI\Support;

use BlackParadise\CoreAdmin\Domain\Action\ActionScope;
use BlackParadise\CoreAdmin\Domain\Contracts\Action\ActionContract;
use BlackParadise\CoreAdmin\Domain\Contracts\Action\ActionResolverContract;
use BlackParadise\CoreAdmin\Domain\Contracts\Auth\AuthorizationProviderContract;
use BlackParadise\CoreAdmin\Domain\Contracts\EntityDefinition\EntityDefinitionContract;
use BlackParadise\CoreAdmin\Domain\Exceptions\InvalidDefinitionException;
use BlackParadise\CoreAdmin\Domain\ValueObjects\Capability;

/**
 * What the UI may render for an entity: capability AND authorization (A41),
 * plus the authorized actions partitioned by scope (A40).
 */
final readonly class EntityUiState
{
    /**
     * @param list<ActionViewModel> $rowActions
     * @param list<ActionViewModel> $bulkActions
     * @param list<ActionViewModel> $entityActions
     */
    private function __construct(
        public bool $canCreate,
        public bool $canUpdate,
        public bool $canDelete,
        public bool $canBulkDelete,
        public array $rowActions,
        public array $bulkActions,
        public array $entityActions,
    ) {}

    public static function for(
        EntityDefinitionContract $definition,
        AuthorizationProviderContract $authorization,
        ActionResolverContract $resolver,
    ): self {
        $writable = $definition->modelClass() !== null;
        $caps     = $definition->capabilities();

        $canCreate = $writable && $caps->allows(Capability::Create) && $authorization->can('create', $definition);
        $canUpdate = $writable && $caps->allows(Capability::Update) && $authorization->can('update', $definition);
        $canDelete = $writable && $caps->allows(Capability::Delete) && $authorization->can('delete', $definition);

        $row = $bulk = $entity = [];
        foreach ($definition->actions() as $actionClass) {
            try {
                $action = $resolver->resolve($actionClass);
            } catch (InvalidDefinitionException $e) {
                throw new InvalidDefinitionException(
                    sprintf('Entity definition "%s": %s', $definition->name(), $e->getMessage()),
                    $e->getCode(),
                    $e,
                );
            }
            $permission = $action->permission() ?? 'action.' . $action->name();
            if (!$authorization->can($permission, $definition)) {
                continue;
            }

            $model = self::viewModel($action, $definition);
            foreach ($action->scopes() as $scope) {
                match ($scope) {
                    ActionScope::Row    => $row[]    = $model,
                    ActionScope::Bulk   => $bulk[]   = $model,
                    ActionScope::Entity => $entity[] = $model,
                };
            }
        }

        return new self($canCreate, $canUpdate, $canDelete, $canDelete, $row, $bulk, $entity);
    }

    private static function viewModel(ActionContract $action, EntityDefinitionContract $definition): ActionViewModel
    {
        $fields = array_values($action->fields());

        return new ActionViewModel(
            name: $action->name(),
            label: $action->label(),
            confirm: $action->confirm(),
            hasForm: $fields !== [],
            url: route('bpadmin.entity.action', ['entity' => $definition->name(), 'action' => $action->name()]),
            fields: $fields,
        );
    }

    public function showBulkBar(): bool
    {
        return $this->canBulkDelete || $this->bulkActions !== [];
    }
}
