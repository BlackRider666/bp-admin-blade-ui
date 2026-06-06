<?php

declare(strict_types=1);

namespace BlackParadise\LaravelAdminBladeUI\Tests\Feature\Views;

use BlackParadise\CoreAdmin\Domain\Fields\MorphToField;
use BlackParadise\LaravelAdminBladeUI\Tests\Fixtures\StubModel;
use BlackParadise\LaravelAdminBladeUI\Tests\TestCase;
use Illuminate\Http\Request;

/**
 * Task C.6 — MorphTo dual type+id picker.
 *
 * The morph-to component must render a TYPE <select> and a dependent ID <select>,
 * driven by Alpine.js with options from meta()['morphOptions'].
 * Both inputs must carry their real DB column names: {name}_type and {name}_id.
 */
final class MorphToComponentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $request = Request::create('/admin/test', 'GET');
        $request->setLaravelSession($this->app['session']->driver());
        $this->app->instance('request', $request);
    }

    private function buildMorphField(): MorphToField
    {
        $field = MorphToField::make('commentable', StubModel::class)
            ->withRelationName('commentable')
            ->withDisplayField('title');

        $field->withMeta([
            'morphOptions' => [
                [
                    'value'   => 'book',
                    'label'   => 'Book',
                    'options' => [
                        ['id' => 1, 'label' => 'Dune'],
                        ['id' => 2, 'label' => 'Foundation'],
                    ],
                ],
                [
                    'value'   => 'article',
                    'label'   => 'Article',
                    'options' => [
                        ['id' => 10, 'label' => 'Post One'],
                    ],
                ],
            ],
        ]);

        return $field;
    }

    /**
     * The component must render a <select> for the type column with
     * name="commentable_type" (the real DB column name).
     */
    public function test_morph_to_renders_type_select_with_correct_name(): void
    {
        $field = $this->buildMorphField();

        $html = view('bpadmin::components.field.morph-to', [
            'field' => $field,
            'value' => null,
            'name'  => 'commentable',
        ])->render();

        self::assertStringContainsString('name="commentable_type"', $html);
    }

    /**
     * The component must render a <select> for the id column with
     * name="commentable_id" (the real DB column name).
     */
    public function test_morph_to_renders_id_select_with_correct_name(): void
    {
        $field = $this->buildMorphField();

        $html = view('bpadmin::components.field.morph-to', [
            'field' => $field,
            'value' => null,
            'name'  => 'commentable',
        ])->render();

        self::assertStringContainsString('name="commentable_id"', $html);
    }

    /**
     * The type labels from morphOptions must appear in the rendered output.
     * Alpine x-for populates the options from the JS `types` array.
     */
    public function test_morph_to_renders_type_labels_in_alpine_data(): void
    {
        $field = $this->buildMorphField();

        $html = view('bpadmin::components.field.morph-to', [
            'field' => $field,
            'value' => null,
            'name'  => 'commentable',
        ])->render();

        // Type label 'Book' must appear in the Alpine x-data JSON or option text.
        self::assertStringContainsString('Book', $html);
    }

    /**
     * The component is now an Alpine-driven picker, NOT the old read-only
     * _relation-single partial. It must not include the read-only placeholder include.
     */
    public function test_morph_to_no_longer_includes_relation_single_readonly(): void
    {
        $field = $this->buildMorphField();

        $html = view('bpadmin::components.field.morph-to', [
            'field' => $field,
            'value' => null,
            'name'  => 'commentable',
        ])->render();

        // The old implementation @included '_relation-single' which rendered a
        // read-only label/value display. The new picker must NOT do that.
        // We check: the old include path is gone. The clearest signal is that
        // a real <select> element exists, which the read-only partial never emits.
        self::assertStringContainsString('<select', $html);
    }

    /**
     * The component must use Alpine x-data and x-for for reactive picker behaviour.
     */
    public function test_morph_to_uses_alpine_x_data_and_x_for(): void
    {
        $field = $this->buildMorphField();

        $html = view('bpadmin::components.field.morph-to', [
            'field' => $field,
            'value' => null,
            'name'  => 'commentable',
        ])->render();

        self::assertStringContainsString('x-data', $html);
        self::assertStringContainsString('x-for', $html);
    }

    /**
     * When morphOptions is empty, the component must still render without error,
     * emitting both selects (with no options beyond the blank placeholder).
     */
    public function test_morph_to_renders_gracefully_with_empty_morph_options(): void
    {
        $field = MorphToField::make('taggable', StubModel::class)
            ->withRelationName('taggable')
            ->withDisplayField('name');

        $field->withMeta(['morphOptions' => []]);

        $html = view('bpadmin::components.field.morph-to', [
            'field' => $field,
            'value' => null,
            'name'  => 'taggable',
        ])->render();

        self::assertStringContainsString('name="taggable_type"', $html);
        self::assertStringContainsString('name="taggable_id"', $html);
    }

    /**
     * When no name prop is passed, the component must fall back to $field->name().
     */
    public function test_morph_to_uses_field_name_when_name_prop_absent(): void
    {
        $field = $this->buildMorphField();

        $html = view('bpadmin::components.field.morph-to', [
            'field' => $field,
            'value' => null,
        ])->render();

        self::assertStringContainsString('name="commentable_type"', $html);
        self::assertStringContainsString('name="commentable_id"', $html);
    }
}
