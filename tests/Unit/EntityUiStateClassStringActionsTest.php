<?php

declare(strict_types=1);

namespace BlackParadise\LaravelAdminBladeUI\Tests\Unit;

use BlackParadise\CoreAdmin\Domain\Contracts\Action\ActionResolverContract;
use BlackParadise\CoreAdmin\Domain\ValueObjects\EntityCapabilities;
use BlackParadise\LaravelAdminBladeUI\Support\EntityUiState;
use BlackParadise\LaravelAdminBladeUI\Tests\Fixtures\DiEntityAction;
use BlackParadise\LaravelAdminBladeUI\Tests\Fixtures\DiRowAction;
use BlackParadise\LaravelAdminBladeUI\Tests\Fixtures\FakeAuthorization;
use BlackParadise\LaravelAdminBladeUI\Tests\Fixtures\UiStateDefinition;
use BlackParadise\LaravelAdminBladeUI\Tests\TestCase;

/** S-1 (spec 08 / U-04): actions() = list<class-string>, resolved via ActionResolverContract. */
final class EntityUiStateClassStringActionsTest extends TestCase
{
    private function definition(): UiStateDefinition
    {
        return new UiStateDefinition(EntityCapabilities::all(), [DiRowAction::class, DiEntityAction::class]);
    }

    public function test_for_class_string_actions_resolves_and_partitions_by_scope(): void
    {
        $ui = EntityUiState::for(
            $this->definition(),
            new FakeAuthorization(['*']),
            $this->app->make(ActionResolverContract::class),
        );

        self::assertSame(['di_row'], array_map(static fn($a) => $a->name, $ui->rowActions));
        self::assertSame(['di_row'], array_map(static fn($a) => $a->name, $ui->bulkActions));
        self::assertSame(['di_entity'], array_map(static fn($a) => $a->name, $ui->entityActions));
    }

    public function test_for_class_string_actions_uses_constructor_injected_dependencies(): void
    {
        $ui = EntityUiState::for(
            $this->definition(),
            new FakeAuthorization(['*']),
            $this->app->make(ActionResolverContract::class),
        );

        self::assertSame('Injected label row', $ui->rowActions[0]->label);
    }

    public function test_for_class_string_actions_delegates_resolution_to_the_given_resolver(): void
    {
        $resolver = $this->createMock(ActionResolverContract::class);
        $resolver->expects(self::exactly(2))->method('resolve')
            ->willReturnCallback(fn(string $c) => $this->app->make($c));

        EntityUiState::for($this->definition(), new FakeAuthorization(['*']), $resolver);
    }

    public function test_for_class_string_actions_filters_by_authorization(): void
    {
        $ui = EntityUiState::for(
            $this->definition(),
            new FakeAuthorization(['action.di_entity']),
            $this->app->make(ActionResolverContract::class),
        );

        self::assertSame([], $ui->rowActions);
        self::assertCount(1, $ui->entityActions);
    }
}
