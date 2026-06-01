<?php

declare(strict_types=1);

namespace BlackParadise\LaravelAdminBladeUI\Tests\Feature\Views;

use BlackParadise\CoreAdmin\Domain\Entity\EntityRecord;
use BlackParadise\CoreAdmin\Domain\Query\PaginatedResult;
use BlackParadise\LaravelAdminBladeUI\Http\Presenters\BladeEntityPresenter;
use BlackParadise\LaravelAdminBladeUI\Tests\Fixtures\StubEntityDefinitionWithRelation;
use BlackParadise\LaravelAdminBladeUI\Tests\TestCase;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Regression test: a belongsTo column whose related model exposes a
 * TRANSLATABLE display field (e.g. City->name = ['en'=>…, 'uk'=>…]) used to
 * reach the list renderer as a raw array and blow up with
 * "htmlspecialchars(): array given" — a 500 on the whole list. The belongsTo
 * display case must resolve the array to a locale string like every other
 * relation column (it already shares the bpDisplayRelation helper).
 */
final class BladeEntityPresenterBelongsToDisplayTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $request = Request::create('/admin/stub_with_relation', 'GET');
        $request->setLaravelSession($this->app['session']->driver());
        $this->app->instance('request', $request);
    }

    public function test_index_renders_belongs_to_with_translatable_display_field(): void
    {
        $definition = new StubEntityDefinitionWithRelation();

        // The related record's display field ('name') is a translatable array —
        // exactly what a City->name belongsTo column delivers.
        $record = new EntityRecord(
            $definition,
            ['id' => 1, 'name' => 'Acme', 'tag_id' => 5],
            ['tag' => ['id' => 5, 'name' => ['en' => 'English City', 'uk' => 'Місто']]],
        );

        $paginated = new PaginatedResult(items: [$record], total: 1, page: 1, perPage: 15);

        $response = $this->presenter()->index($paginated, $definition->fields(), $definition);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());

        $body = (string) $response->getContent();
        // Locale string resolved, not a raw array dumped / crashed.
        self::assertStringContainsString('English City', $body);
        self::assertStringNotContainsString('htmlspecialchars', $body);
    }

    private function presenter(): BladeEntityPresenter
    {
        return new BladeEntityPresenter();
    }
}
