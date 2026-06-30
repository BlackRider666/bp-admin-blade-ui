<?php

declare(strict_types=1);

namespace BlackParadise\LaravelAdminBladeUI\Tests\Feature\Views;

use BlackParadise\CoreAdmin\Domain\Fields\BelongsToField;
use BlackParadise\CoreAdmin\Domain\Fields\HasManyField;
use BlackParadise\CoreAdmin\Domain\Fields\TextField;
use BlackParadise\LaravelAdmin\EntityDefinition;
use BlackParadise\LaravelAdminBladeUI\Http\Presenters\BladeEntityPresenter;
use BlackParadise\LaravelAdminBladeUI\Tests\Fixtures\StubJournalDefinition;
use BlackParadise\LaravelAdminBladeUI\Tests\Fixtures\StubJournalHistoryDefinition;
use BlackParadise\LaravelAdminBladeUI\Tests\Fixtures\StubModel;
use BlackParadise\LaravelAdminBladeUI\Tests\Fixtures\Tag;
use BlackParadise\LaravelAdminBladeUI\Tests\TestCase;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Regression test for Bug #2: embedded relation selects are empty because
 * the view re-resolves undecorated fields via EntityDefinitionRegistry instead
 * of reading $field->meta()['embeddedFields'] which Core attaches with options.
 *
 * CONTRACT:
 * When Core's BuildFormViewUseCase decorates an embedded container field,
 * it attaches sub-fields (already filtered visibleOnForm + with options) as
 * meta()['embeddedFields']. The Blade view must read that key and must NOT
 * re-instantiate the definition from the registry (which would lose options).
 *
 * This test simulates what Core produces: a HasManyField whose meta()['embeddedFields']
 * contains a BelongsToField already decorated with meta()['options']. The rendered
 * HTML must contain those <option> elements — NOT "No options available.".
 */
final class EmbeddedBelongsToOptionsRenderTest extends TestCase
{
    private BladeEntityPresenter $presenter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->presenter = new BladeEntityPresenter();

        $request = Request::create('/admin/stub_journal/create', 'GET');
        $request->setLaravelSession($this->app['session']->driver());
        $this->app->instance('request', $request);
    }

    /**
     * Build a hasMany container field with a pre-decorated embedded sub-field
     * that carries belongsTo options — exactly the payload Core would attach.
     */
    private function buildDecoratedHasManyField(): HasManyField
    {
        // Embedded sub-field: a belongsTo that Core has already decorated with options.
        // Target is Tag::class (not StubModel::class) so the FK-skip filter in Blade
        // does not strip it — FK-skip only removes fields where target === $hostModelClass.
        $categoryField = BelongsToField::make('category_id', Tag::class)
            ->withRelationName('category')
            ->withDisplayField('name');

        $categoryField->withMeta([
            'options' => [
                ['id' => 10, 'label' => 'Fiction'],
                ['id' => 20, 'label' => 'Non-Fiction'],
                ['id' => 30, 'label' => 'Science'],
            ],
        ]);

        $titleField = TextField::make('title')->required();

        // Container field carries the already-decorated sub-fields via meta.
        $hasManyField = HasManyField::make('items', StubModel::class)
            ->withRelationName('items')
            ->embed(StubJournalDefinition::class) // definition class exists (required by embed())
            ->strategy('merge');

        $hasManyField->withMeta([
            'embeddedFields' => [$titleField, $categoryField],
        ]);

        return $hasManyField;
    }

    public function test_create_renders_embedded_belongs_to_options_from_meta(): void
    {
        // Build a stub definition that exposes the decorated container field.
        $definition = new class extends EntityDefinition {
            public string $model = StubModel::class;

            public function resolveName(): string
            {
                return 'stub_with_embedded_belongs_to';
            }

            public function fields(): array
            {
                // Deliberately empty — caller overrides $fields externally.
                return [];
            }
        };

        $fields = [
            TextField::make('name'),
            $this->buildDecoratedHasManyField(),
        ];

        $response = $this->presenter->create($fields, $definition);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        $body = (string) $response->getContent();

        // The embedded belongsTo select must contain the option labels provided in meta.
        self::assertStringContainsString('Fiction', $body);
        self::assertStringContainsString('Non-Fiction', $body);
        self::assertStringContainsString('Science', $body);

        // Must NOT fall back to the "No options available." placeholder.
        self::assertStringNotContainsString('No options available.', $body);

        // Must still render as a repeater (namePrefix/repeater not broken).
        self::assertStringContainsString('bpRepeater', $body);
    }

    public function test_create_renders_only_fields_from_meta_not_embedded_definition_fields(): void
    {
        // Post Task 2.3 IMPL, the view reads ONLY meta()['embeddedFields'] supplied by Core.
        // Fields defined in the embedded definition class that are absent from meta must NOT
        // appear in the rendered output.
        //
        // This test documents the new contract and guards against regression to the old fallback.
        $definition = new class extends EntityDefinition {
            public string $model = StubModel::class;

            public function resolveName(): string
            {
                return 'stub_meta_only_contract';
            }

            public function fields(): array
            {
                return [];
            }
        };

        // meta provides ONLY 'title' — deliberately excludes 'period' and 'events'
        // which StubJournalHistoryDefinition::fields() would supply via the old fallback.
        $titleField = TextField::make('title')->required();
        $hasManyField = HasManyField::make('journals', StubModel::class)
            ->withRelationName('journals')
            ->embed(StubJournalHistoryDefinition::class)
            ->strategy('merge');

        $hasManyField->withMeta(['embeddedFields' => [$titleField]]);

        $response = $this->presenter->create([$hasManyField], $definition);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        $body = (string) $response->getContent();

        // The repeater must render (meta path is active).
        self::assertStringContainsString('bpRepeater', $body);

        // 'period' is in StubJournalHistoryDefinition::fields() but NOT in meta — must not appear.
        self::assertStringNotContainsString('name="journals[__ROW__][period]"', $body);
    }
}
