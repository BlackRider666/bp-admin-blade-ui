<?php

declare(strict_types=1);

namespace BlackParadise\LaravelAdminBladeUI\Tests\Unit;

use BlackParadise\CoreAdmin\Domain\Action\ActionScope;
use BlackParadise\CoreAdmin\Domain\Contracts\Action\ActionResolverContract;
use BlackParadise\CoreAdmin\Domain\Fields\TextField;
use BlackParadise\CoreAdmin\Domain\ValueObjects\Capability;
use BlackParadise\CoreAdmin\Domain\ValueObjects\EntityCapabilities;
use BlackParadise\LaravelAdminBladeUI\Support\ActionViewModel;
use BlackParadise\LaravelAdminBladeUI\Support\EntityUiState;
use BlackParadise\LaravelAdminBladeUI\Tests\Fixtures\FakeAuthorization;
use BlackParadise\LaravelAdminBladeUI\Tests\Fixtures\UiStateAction;
use BlackParadise\LaravelAdminBladeUI\Tests\Fixtures\UiStateDefinition;
use BlackParadise\LaravelAdminBladeUI\Tests\TestCase;

/**
 * A41 / A29 / A40: EntityUiState = capability AND authorization; actions filtered by can(permission).
 *
 * Assumed API: EntityUiState::for(EntityDefinitionContract, AuthorizationProviderContract, ActionResolverContract): self
 * (definition->actions() is list<class-string<ActionContract>>, resolved via the resolver)
 * with public readonly bool canCreate/canUpdate/canDelete/canBulkDelete and
 * public readonly list<ActionViewModel> rowActions/bulkActions/entityActions.
 */
final class EntityUiStateTest extends TestCase
{
    public function test_for_when_capabilities_and_authz_allow_all_enables_every_flag(): void
    {
        $definition = new UiStateDefinition();

        $ui = EntityUiState::for($definition, new FakeAuthorization(['*']), $this->resolver());

        self::assertTrue($ui->canCreate);
        self::assertTrue($ui->canUpdate);
        self::assertTrue($ui->canDelete);
        self::assertTrue($ui->canBulkDelete);
    }

    public function test_for_when_authz_denies_create_disables_only_can_create_even_if_capability_allows(): void
    {
        $definition = new UiStateDefinition();

        $ui = EntityUiState::for($definition, FakeAuthorization::denying('create'), $this->resolver());

        self::assertFalse($ui->canCreate);
        self::assertTrue($ui->canUpdate);
        self::assertTrue($ui->canDelete);
    }

    public function test_for_when_authz_denies_update_disables_can_update(): void
    {
        $ui = EntityUiState::for(new UiStateDefinition(), FakeAuthorization::denying('update'), $this->resolver());

        self::assertFalse($ui->canUpdate);
        self::assertTrue($ui->canCreate);
    }

    public function test_for_when_authz_denies_delete_disables_delete_and_bulk_delete(): void
    {
        $ui = EntityUiState::for(new UiStateDefinition(), FakeAuthorization::denying('delete'), $this->resolver());

        self::assertFalse($ui->canDelete);
        self::assertFalse($ui->canBulkDelete);
    }

    public function test_for_when_capability_denies_update_disables_can_update_even_if_authz_allows(): void
    {
        $definition = new UiStateDefinition(EntityCapabilities::all()->without(Capability::Update));

        $ui = EntityUiState::for($definition, new FakeAuthorization(['*']), $this->resolver());

        self::assertFalse($ui->canUpdate);
        self::assertTrue($ui->canCreate);
    }

    public function test_for_when_capability_denies_create_disables_can_create_even_if_authz_allows(): void
    {
        $definition = new UiStateDefinition(EntityCapabilities::all()->without(Capability::Create));

        $ui = EntityUiState::for($definition, new FakeAuthorization(['*']), $this->resolver());

        self::assertFalse($ui->canCreate);
    }

    public function test_for_when_capability_denies_delete_disables_delete_and_bulk_delete(): void
    {
        $definition = new UiStateDefinition(EntityCapabilities::all()->without(Capability::Delete));

        $ui = EntityUiState::for($definition, new FakeAuthorization(['*']), $this->resolver());

        self::assertFalse($ui->canDelete);
        self::assertFalse($ui->canBulkDelete);
    }

    public function test_for_when_read_only_all_flags_are_false_even_if_authz_allows_everything(): void
    {
        $definition = new UiStateDefinition(EntityCapabilities::readOnly());

        $ui = EntityUiState::for($definition, new FakeAuthorization(['*']), $this->resolver());

        self::assertFalse($ui->canCreate);
        self::assertFalse($ui->canUpdate);
        self::assertFalse($ui->canDelete);
        self::assertFalse($ui->canBulkDelete);
    }

    public function test_for_when_model_less_all_flags_are_false(): void
    {
        $definition = new UiStateDefinition(EntityCapabilities::all(), [], modelLess: true);

        $ui = EntityUiState::for($definition, new FakeAuthorization(['*']), $this->resolver());

        self::assertNull($definition->modelClass());
        self::assertFalse($ui->canCreate);
        self::assertFalse($ui->canUpdate);
        self::assertFalse($ui->canDelete);
        self::assertFalse($ui->canBulkDelete);
    }

    public function test_for_partitions_actions_by_scope(): void
    {
        $definition = new UiStateDefinition(EntityCapabilities::all(), [
            new UiStateAction('only_row', [ActionScope::Row]),
            new UiStateAction('only_bulk', [ActionScope::Bulk]),
            new UiStateAction('only_entity', [ActionScope::Entity]),
            new UiStateAction('row_and_bulk', [ActionScope::Row, ActionScope::Bulk]),
        ]);

        $ui = EntityUiState::for($definition, new FakeAuthorization(['*']), $this->resolver());

        self::assertSame(['only_row', 'row_and_bulk'], $this->names($ui->rowActions));
        self::assertSame(['only_bulk', 'row_and_bulk'], $this->names($ui->bulkActions));
        self::assertSame(['only_entity'], $this->names($ui->entityActions));
    }

    public function test_for_hides_action_when_default_permission_denied(): void
    {
        $definition = new UiStateDefinition(EntityCapabilities::all(), [
            new UiStateAction('export', [ActionScope::Entity]),
            new UiStateAction('purge', [ActionScope::Entity]),
        ]);

        $ui = EntityUiState::for($definition, new FakeAuthorization(['action.export']), $this->resolver());

        self::assertSame(['export'], $this->names($ui->entityActions));
    }

    public function test_for_uses_explicit_permission_instead_of_default_ability(): void
    {
        $definition = new UiStateDefinition(EntityCapabilities::all(), [
            new UiStateAction('export', [ActionScope::Entity], permission: 'reports.export'),
        ]);

        $withExplicit = EntityUiState::for($definition, new FakeAuthorization(['reports.export']), $this->resolver());
        $withDefaultOnly = EntityUiState::for($definition, new FakeAuthorization(['action.export']), $this->resolver());

        self::assertSame(['export'], $this->names($withExplicit->entityActions));
        self::assertSame([], $withDefaultOnly->entityActions);
    }

    public function test_for_keeps_authorized_actions_on_read_only_definition(): void
    {
        $definition = new UiStateDefinition(EntityCapabilities::readOnly(), [
            new UiStateAction('export', [ActionScope::Entity]),
        ]);

        $ui = EntityUiState::for($definition, new FakeAuthorization(['*']), $this->resolver());

        self::assertSame(['export'], $this->names($ui->entityActions));
    }

    public function test_action_view_model_exposes_name_label_confirm_has_form_and_url(): void
    {
        $definition = new UiStateDefinition(EntityCapabilities::all(), [
            new UiStateAction('ban', [ActionScope::Entity], confirm: true, fields: [TextField::make('reason')]),
            new UiStateAction('ping', [ActionScope::Entity]),
        ]);

        $ui = EntityUiState::for($definition, new FakeAuthorization(['*']), $this->resolver());
        [$ban, $ping] = $ui->entityActions;

        self::assertInstanceOf(ActionViewModel::class, $ban);
        self::assertSame('ban', $ban->name);
        self::assertSame('Label ban', $ban->label);
        self::assertTrue($ban->confirm);
        self::assertTrue($ban->hasForm);
        self::assertSame(route('bpadmin.entity.action', ['entity' => 'ui_thing', 'action' => 'ban']), $ban->url);
        self::assertFalse($ping->confirm);
        self::assertFalse($ping->hasForm);
    }

    public function test_row_action_view_model_url_targets_the_action_endpoint(): void
    {
        $definition = new UiStateDefinition(EntityCapabilities::all(), [
            new UiStateAction('archive', [ActionScope::Row]),
        ]);

        $ui = EntityUiState::for($definition, new FakeAuthorization(['*']), $this->resolver());

        self::assertStringContainsString('/actions/archive', $ui->rowActions[0]->url);
    }

    private function resolver(): ActionResolverContract
    {
        return $this->app->make(ActionResolverContract::class);
    }

    /**
     * @param list<ActionViewModel> $actions
     * @return list<string>
     */
    private function names(array $actions): array
    {
        return array_map(static fn(ActionViewModel $a): string => $a->name, $actions);
    }
}
