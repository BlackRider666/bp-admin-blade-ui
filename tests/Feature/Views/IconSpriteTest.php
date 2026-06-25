<?php

declare(strict_types=1);

namespace BlackParadise\LaravelAdminBladeUI\Tests\Feature\Views;

use BlackParadise\CoreAdmin\Domain\Entity\EntityRecord;
use BlackParadise\CoreAdmin\Domain\Fields\DateTimeField;
use BlackParadise\CoreAdmin\Domain\Fields\TextField;
use BlackParadise\CoreAdmin\Domain\Query\PaginatedResult;
use BlackParadise\LaravelAdmin\EntityDefinition;
use BlackParadise\LaravelAdminBladeUI\Http\Presenters\BladeEntityPresenter;
use BlackParadise\LaravelAdminBladeUI\Tests\Fixtures\StubModel;
use BlackParadise\LaravelAdminBladeUI\Tests\TestCase;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Task 15 — SVG sprite smoke test.
 *
 * Asserts that:
 *  1. The layout body contains the shared <symbol> sprite definitions.
 *  2. Row-action SVGs reference the sprite via <use href="#bp-i-..."> instead
 *     of inlining path data.
 *  3. The datetime clock icon also references the sprite.
 *  4. The old inline eye-path M15 12a3 is NOT present in the row action area.
 */
final class IconSpriteTest extends TestCase
{
    private BladeEntityPresenter $presenter;

    private EntityDefinition $definition;

    protected function setUp(): void
    {
        parent::setUp();

        $this->presenter = new BladeEntityPresenter();

        $this->definition = new class extends EntityDefinition {
            public string $model = StubModel::class;

            public function resolveName(): string
            {
                return 'stub_sprite';
            }

            public function fields(): array
            {
                return [
                    TextField::make('name'),
                    DateTimeField::make('created_at'),
                ];
            }
        };

        $request = Request::create('/admin/stub_sprite', 'GET');
        $request->setLaravelSession($this->app['session']->driver());
        $this->app->instance('request', $request);
    }

    /**
     * Renders the index view with one record so the row action block is
     * included in the output (the @forelse body executes).
     */
    private function renderIndex(): string
    {
        $record = new EntityRecord($this->definition, [
            'id'         => 42,
            'name'       => 'Sprite Test',
            'created_at' => '2026-06-17 10:00:00',
        ]);

        $paginated = new PaginatedResult(
            items: [$record],
            total: 1,
            page: 1,
            perPage: 15,
        );

        $response = $this->presenter->index(
            $paginated,
            $this->definition->fields(),
            $this->definition,
        );

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());

        return (string) $response->getContent();
    }

    /**
     * Renders the index view with TWO records so that per-row SVG path
     * duplication would produce count > 1, while the sprite approach keeps
     * each path exactly once (inside <symbol> only).
     */
    private function renderIndexTwoRecords(): string
    {
        $record1 = new EntityRecord($this->definition, [
            'id'         => 42,
            'name'       => 'Sprite Test',
            'created_at' => '2026-06-17 10:00:00',
        ]);

        $record2 = new EntityRecord($this->definition, [
            'id'         => 43,
            'name'       => 'Sprite Test 2',
            'created_at' => '2026-06-17 11:00:00',
        ]);

        $paginated = new PaginatedResult(
            items: [$record1, $record2],
            total: 2,
            page: 1,
            perPage: 15,
        );

        $response = $this->presenter->index(
            $paginated,
            $this->definition->fields(),
            $this->definition,
        );

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());

        return (string) $response->getContent();
    }

    // ------------------------------------------------------------------
    // Sprite <symbol> definitions in layout
    // ------------------------------------------------------------------

    public function test_layout_contains_view_symbol(): void
    {
        $html = $this->renderIndex();

        self::assertStringContainsString('<symbol id="bp-i-view"', $html);
    }

    public function test_layout_contains_edit_symbol(): void
    {
        $html = $this->renderIndex();

        self::assertStringContainsString('<symbol id="bp-i-edit"', $html);
    }

    public function test_layout_contains_trash_symbol(): void
    {
        $html = $this->renderIndex();

        self::assertStringContainsString('<symbol id="bp-i-trash"', $html);
    }

    public function test_layout_contains_clock_symbol(): void
    {
        $html = $this->renderIndex();

        self::assertStringContainsString('<symbol id="bp-i-clock"', $html);
    }

    // ------------------------------------------------------------------
    // Row action block uses sprite <use href> references
    // ------------------------------------------------------------------

    public function test_row_action_view_uses_sprite_reference(): void
    {
        $html = $this->renderIndex();

        self::assertStringContainsString('<use href="#bp-i-view"', $html);
    }

    public function test_row_action_edit_uses_sprite_reference(): void
    {
        $html = $this->renderIndex();

        self::assertStringContainsString('<use href="#bp-i-edit"', $html);
    }

    public function test_row_action_trash_uses_sprite_reference(): void
    {
        $html = $this->renderIndex();

        self::assertStringContainsString('<use href="#bp-i-trash"', $html);
    }

    // ------------------------------------------------------------------
    // Datetime cell clock icon uses sprite
    // ------------------------------------------------------------------

    public function test_datetime_cell_clock_uses_sprite_reference(): void
    {
        $html = $this->renderIndex();

        self::assertStringContainsString('<use href="#bp-i-clock"', $html);
    }

    // ------------------------------------------------------------------
    // Row action block SVGs use <use href> — no standalone <path> in action cell
    // (confirms that per-row path duplication is gone; path lives only in sprite)
    // ------------------------------------------------------------------

    public function test_row_action_view_svg_has_no_standalone_path_element(): void
    {
        // TWO records: with old per-row inline SVGs the eye path would appear
        // twice (once per row); with the sprite approach it appears exactly
        // once — inside the <symbol> definition only.
        $html = $this->renderIndexTwoRecords();

        $occurrences = substr_count($html, 'M15 12a3 3 0 11-6 0 3 3 0 016 0z');
        self::assertSame(1, $occurrences, 'Eye path should appear exactly once (in <symbol>), not per row');
    }
}
