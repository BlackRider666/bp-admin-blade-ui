<?php

declare(strict_types=1);

namespace BlackParadise\LaravelAdminBladeUI\Tests\Feature\Views;

use BlackParadise\CoreAdmin\Domain\ValueObjects\EntityCapabilities;
use BlackParadise\LaravelAdminBladeUI\Tests\Feature\Views\Concerns\RendersUiPages;
use BlackParadise\LaravelAdminBladeUI\Tests\Fixtures\DiEntityAction;
use BlackParadise\LaravelAdminBladeUI\Tests\Fixtures\DiRowAction;
use BlackParadise\LaravelAdminBladeUI\Tests\Fixtures\FakeAuthorization;
use BlackParadise\LaravelAdminBladeUI\Tests\Fixtures\UiStateDefinition;
use BlackParadise\LaravelAdminBladeUI\Tests\TestCase;

/** S-1: index for a definition whose actions() are class-strings must render (was 500). */
final class ClassStringActionsRenderingTest extends TestCase
{
    use RendersUiPages;

    private function definition(): UiStateDefinition
    {
        return new UiStateDefinition(EntityCapabilities::all(), [DiRowAction::class, DiEntityAction::class]);
    }

    public function test_index_with_class_string_actions_renders_entity_action_control(): void
    {
        $html = $this->renderIndex($this->definition(), new FakeAuthorization(['*']));

        self::assertStringContainsString(
            'action="' . route('bpadmin.entity.action', ['entity' => 'ui_thing', 'action' => 'di_entity']) . '"',
            $html,
        );
    }

    public function test_index_with_class_string_actions_renders_row_action_control(): void
    {
        $html = $this->renderIndex($this->definition(), new FakeAuthorization(['*']));

        self::assertStringContainsString('data-bp-row-actions', $html);
        self::assertStringContainsString(
            '/actions/di_row',
            $html,
        );
    }

    public function test_index_with_class_string_actions_renders_bulk_action_control_with_injected_label(): void
    {
        $html = $this->renderIndex($this->definition(), new FakeAuthorization(['*']), rows: 2);

        self::assertStringContainsString('Injected label row', $html);
        self::assertStringContainsString('keys[]', $html);
    }
}
