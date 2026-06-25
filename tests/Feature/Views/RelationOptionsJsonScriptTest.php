<?php

declare(strict_types=1);

namespace BlackParadise\LaravelAdminBladeUI\Tests\Feature\Views;

use BlackParadise\CoreAdmin\Domain\Fields\BelongsToManyField;
use BlackParadise\LaravelAdmin\Http\Presenters\EntityPresenterInterface;
use BlackParadise\LaravelAdminBladeUI\Http\Presenters\BladeEntityPresenter;
use BlackParadise\LaravelAdminBladeUI\Tests\Fixtures\StubEntityDefinitionWithRelation;
use BlackParadise\LaravelAdminBladeUI\Tests\Fixtures\Tag;
use BlackParadise\LaravelAdminBladeUI\Tests\TestCase;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Verifies that belongs-to and belongs-to-many fields render options
 * via a <script type="application/json"> tag (not inline in x-data)
 * and that x-data uses the named Alpine components bpRelationSelect /
 * bpRelationMultiSelect.
 *
 * Task 17 — RED test written BEFORE implementation.
 */
final class RelationOptionsJsonScriptTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $request = Request::create('/admin/stub_with_relation/create', 'GET');
        $request->setLaravelSession($this->app['session']->driver());
        $this->app->instance('request', $request);
    }

    public function test_belongs_to_renders_options_via_json_script_tag(): void
    {
        $definition = new StubEntityDefinitionWithRelation();
        $fields     = $definition->fields();

        foreach ($fields as $field) {
            if ($field->name() === 'tag_id') {
                $field->withMeta(['options' => [
                    ['id' => 1, 'label' => 'Tech'],
                    ['id' => 2, 'label' => 'News'],
                ]]);
            }
        }

        $presenter = $this->app->make(EntityPresenterInterface::class);
        self::assertInstanceOf(BladeEntityPresenter::class, $presenter);

        $response = $presenter->create($fields, $definition);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());

        $html = (string) $response->getContent();

        // JSON-script tag must be present
        self::assertStringContainsString('type="application/json"', $html);
        self::assertStringContainsString('id="bp-opts-', $html);

        // Named Alpine component call must be present
        self::assertStringContainsString("bpRelationSelect('bp-opts-", $html);

        // Options JSON payload must actually land inside the <script> body
        self::assertStringContainsString('"label":"Tech"', $html);

        // Old inline x-data getter must NOT be present (now lives only in the JS bundle)
        self::assertStringNotContainsString('get filteredOptions()', $html);
    }

    public function test_belongs_to_many_renders_options_via_json_script_tag(): void
    {
        $definition = new StubEntityDefinitionWithRelation();

        // Build a fresh field list that includes a BelongsToManyField
        $manyField = BelongsToManyField::make('tag_ids', Tag::class)
            ->withRelationName('tags')
            ->withDisplayField('name');

        $manyField->withMeta(['options' => [
            ['id' => 1, 'label' => 'Alpha'],
            ['id' => 2, 'label' => 'Beta'],
        ]]);

        $fields = [$manyField];

        $presenter = $this->app->make(EntityPresenterInterface::class);
        self::assertInstanceOf(BladeEntityPresenter::class, $presenter);

        $response = $presenter->create($fields, $definition);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());

        $html = (string) $response->getContent();

        // JSON-script tag must be present
        self::assertStringContainsString('type="application/json"', $html);
        self::assertStringContainsString('id="bp-opts-', $html);

        // Named Alpine multi-select component call must be present
        self::assertStringContainsString('bpRelationMultiSelect(', $html);

        // Options JSON payload must actually land inside the <script> body
        self::assertStringContainsString('"label":"Alpha"', $html);

        // Old inline x-data getter must NOT be present (now lives only in the JS bundle)
        self::assertStringNotContainsString('get filteredOptions()', $html);
    }
}
