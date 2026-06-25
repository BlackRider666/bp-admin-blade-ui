<?php

declare(strict_types=1);

namespace BlackParadise\LaravelAdminBladeUI\Tests\Feature\Views;

use BlackParadise\CoreAdmin\Domain\Entity\EntityRecord;
use BlackParadise\CoreAdmin\Domain\Fields\TextField;
use BlackParadise\CoreAdmin\Domain\Query\PaginatedResult;
use BlackParadise\LaravelAdmin\EntityDefinition;
use BlackParadise\LaravelAdminBladeUI\Http\Presenters\BladeEntityPresenter;
use BlackParadise\LaravelAdminBladeUI\Tests\Fixtures\StubModel;
use BlackParadise\LaravelAdminBladeUI\Tests\TestCase;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * RED test for Task A / Change #9: Google Fonts CSS preload resource hint.
 *
 * A <link rel="preload" as="style"> hint for the Google Fonts CSS URL should be
 * emitted immediately before the existing <link rel="stylesheet"> in the layout
 * head. The preload hint accelerates font discovery without changing behavior
 * (the stylesheet link remains and does the actual load).
 *
 * Before the edit: no preload tag in layout → honest RED.
 * After the edit:  preload tag with as="style" present → GREEN.
 */
final class FontPreloadHintTest extends TestCase
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
                return 'stub_preload';
            }

            public function fields(): array
            {
                return [
                    TextField::make('name'),
                ];
            }
        };

        $request = Request::create('/admin/stub_preload', 'GET');
        $request->setLaravelSession($this->app['session']->driver());
        $this->app->instance('request', $request);
    }

    private function renderLayout(): string
    {
        $record = new EntityRecord($this->definition, ['id' => 1, 'name' => 'Preload Test']);

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

    public function test_layout_head_contains_google_fonts_preload_hint(): void
    {
        $html = $this->renderLayout();

        // Assert a <link rel="preload" as="style"> tag exists for the fonts URL.
        self::assertStringContainsString(
            'rel="preload"',
            $html,
            'Layout must contain a <link rel="preload"> hint for Google Fonts CSS.',
        );

        self::assertStringContainsString(
            'as="style"',
            $html,
            'Preload hint must have as="style" to target a stylesheet resource.',
        );

        // Confirm the preload points at the same Google Fonts URL as the stylesheet.
        self::assertStringContainsString(
            'https://fonts.googleapis.com/css2?family=Assistant',
            $html,
            'Preload href must match the Google Fonts CSS URL used by rel="stylesheet".',
        );
    }
}
