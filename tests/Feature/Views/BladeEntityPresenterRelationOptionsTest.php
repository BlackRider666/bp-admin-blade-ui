<?php

declare(strict_types=1);

namespace BlackParadise\LaravelAdminBladeUI\Tests\Feature\Views;

use BlackParadise\LaravelAdmin\Http\Presenters\EntityPresenterInterface;
use BlackParadise\LaravelAdminBladeUI\Http\Presenters\BladeEntityPresenter;
use BlackParadise\LaravelAdminBladeUI\Tests\Fixtures\StubEntityDefinitionWithRelation;
use BlackParadise\LaravelAdminBladeUI\Tests\TestCase;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Verifies that the Blade `create` view renders relation options when
 * `meta['options']` is already populated on a relation field.
 *
 * Note: BladeEntityPresenter no longer queries option sources itself —
 * `BuildFormViewUseCase` performs that via `RelationOptionsProviderContract`
 * (Application layer). This test therefore pre-populates meta directly,
 * mirroring what the use case produces, and asserts the view renders
 * each option label.
 */
final class BladeEntityPresenterRelationOptionsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $request = Request::create('/admin/stub_with_relation/create', 'GET');
        $request->setLaravelSession($this->app['session']->driver());
        $this->app->instance('request', $request);
    }

    public function test_create_renders_relation_options_when_meta_is_populated(): void
    {
        $definition = new StubEntityDefinitionWithRelation();
        $fields     = $definition->fields();

        // Simulate what BuildFormViewUseCase + RelationOptionsProviderContract
        // would attach upstream: meta['options'] as a list of {id, label}.
        foreach ($fields as $field) {
            if ($field->name() === 'tag_id') {
                $field->withMeta(['options' => [
                    ['id' => 1, 'label' => 'Tech'],
                    ['id' => 2, 'label' => 'News'],
                    ['id' => 3, 'label' => 'Sport'],
                ]]);
            }
        }

        $presenter = $this->app->make(EntityPresenterInterface::class);
        self::assertInstanceOf(BladeEntityPresenter::class, $presenter);

        $response = $presenter->create($fields, $definition);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());

        $body = (string) $response->getContent();
        self::assertStringContainsString('Tech', $body);
        self::assertStringContainsString('News', $body);
        self::assertStringContainsString('Sport', $body);
    }
}
