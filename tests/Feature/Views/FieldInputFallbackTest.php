<?php

declare(strict_types=1);

namespace BlackParadise\LaravelAdminBladeUI\Tests\Feature\Views;

use BlackParadise\CoreAdmin\Domain\Fields\Base\AbstractField;
use BlackParadise\CoreAdmin\Domain\Fields\HasManyField;
use BlackParadise\CoreAdmin\Domain\Fields\HasOneField;
use BlackParadise\CoreAdmin\Domain\Fields\MorphManyField;
use BlackParadise\CoreAdmin\Domain\Fields\MorphToField;
use BlackParadise\LaravelAdminBladeUI\Tests\Fixtures\StubEntityDefinition;
use BlackParadise\LaravelAdminBladeUI\Tests\Fixtures\StubModel;
use BlackParadise\LaravelAdminBladeUI\Tests\TestCase;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ViewErrorBag;

/**
 * Regression test for Bug #1: form render must not throw a ViewNotFound 500
 * when a field type has no dedicated form template.
 *
 * - Unknown types must fall back to _unsupported.blade.php.
 * - Known read-only relation types (has-many, has-one, morph-many, morph-to)
 *   must render without error via their dedicated read-only views.
 */
final class FieldInputFallbackTest extends TestCase
{
    private StubEntityDefinition $definition;

    protected function setUp(): void
    {
        parent::setUp();
        $this->definition = new StubEntityDefinition();
    }

    public function test_field_input_has_many_renders_without_error(): void
    {
        $field = HasManyField::make('comments', StubModel::class)
            ->withRelationName('comments');

        $html = $this->renderFieldInput($field, [['id' => 1, 'body' => 'Hello']]);

        self::assertStringNotContainsString('ViewNotFound', $html);
    }

    public function test_field_input_has_one_renders_without_error(): void
    {
        $field = HasOneField::make('profile', StubModel::class)
            ->withRelationName('profile');

        $html = $this->renderFieldInput($field, ['id' => 1, 'bio' => 'Test bio']);

        self::assertStringNotContainsString('ViewNotFound', $html);
    }

    public function test_field_input_morph_many_renders_without_error(): void
    {
        $field = MorphManyField::make('images', StubModel::class)
            ->withRelationName('images');

        $html = $this->renderFieldInput($field, [['id' => 1, 'url' => 'http://example.com/img.jpg']]);

        self::assertStringNotContainsString('ViewNotFound', $html);
    }

    public function test_field_input_morph_to_renders_without_error(): void
    {
        $field = MorphToField::make('taggable', StubModel::class)
            ->withRelationName('taggable');

        $html = $this->renderFieldInput($field, ['id' => 5, 'name' => 'Article']);

        self::assertStringNotContainsString('ViewNotFound', $html);
    }

    public function test_field_input_unknown_type_renders_unsupported_notice(): void
    {
        // A field whose type() returns something for which no blade view exists.
        $field = new class extends AbstractField {
            public function __construct()
            {
                parent::__construct('mystery_field', 'Mystery', [], true, true, true, false, false, []);
            }

            public static function make(string $name): static
            {
                return new static();
            }

            public function type(): string
            {
                return 'totally_unknown_type_xyz';
            }
        };

        $html = $this->renderFieldInput($field, null);

        // Must render the unsupported-field notice, not crash with ViewNotFound.
        self::assertStringContainsString('not supported', strtolower($html));
        self::assertStringContainsString('totally_unknown_type_xyz', $html);
    }

    public function test_field_input_has_many_renders_related_item_label(): void
    {
        $field = HasManyField::make('tags', StubModel::class)
            ->withRelationName('tags');

        $html = $this->renderFieldInput($field, [
            ['id' => 1, 'name' => 'php'],
            ['id' => 2, 'name' => 'laravel'],
        ]);

        self::assertStringContainsString('php', $html);
        self::assertStringContainsString('laravel', $html);
    }

    public function test_field_input_has_one_renders_dash_when_empty(): void
    {
        $field = HasOneField::make('profile', StubModel::class)
            ->withRelationName('profile');

        $html = $this->renderFieldInput($field, null);

        self::assertStringContainsString('—', $html);
    }

    // ------------------------------------------------------------------
    // H5 regression: translatable display field must NOT render 'Array'
    // ------------------------------------------------------------------

    public function test_has_many_translatable_display_field_renders_locale_string(): void
    {
        $field = HasManyField::make('cities', StubModel::class)
            ->withRelationName('cities')
            ->withDisplayField('name');

        // 'name' is a translatable array — the bug: this used to render 'Array'
        $html = $this->renderFieldInput($field, [
            ['id' => 1, 'name' => ['en' => 'London', 'uk' => 'Лондон']],
        ]);

        self::assertStringContainsString('London', $html);
        self::assertStringNotContainsString('Array', $html);
    }

    public function test_morph_many_translatable_display_field_renders_locale_string(): void
    {
        $field = MorphManyField::make('cities', StubModel::class)
            ->withRelationName('cities')
            ->withDisplayField('name');

        $html = $this->renderFieldInput($field, [
            ['id' => 2, 'name' => ['en' => 'Paris', 'uk' => 'Париж']],
        ]);

        self::assertStringContainsString('Paris', $html);
        self::assertStringNotContainsString('Array', $html);
    }

    public function test_has_one_translatable_display_field_renders_locale_string(): void
    {
        $field = HasOneField::make('city', StubModel::class)
            ->withRelationName('city')
            ->withDisplayField('name');

        $html = $this->renderFieldInput($field, ['id' => 3, 'name' => ['en' => 'Berlin', 'uk' => 'Берлін']]);

        self::assertStringContainsString('Berlin', $html);
        self::assertStringNotContainsString('Array', $html);
    }

    public function test_morph_to_translatable_display_field_renders_locale_string(): void
    {
        $field = MorphToField::make('location', StubModel::class)
            ->withRelationName('location')
            ->withDisplayField('name');

        $html = $this->renderFieldInput($field, ['id' => 4, 'name' => ['en' => 'Kyiv', 'uk' => 'Київ']]);

        self::assertStringContainsString('Kyiv', $html);
        self::assertStringNotContainsString('Array', $html);
    }

    public function test_has_many_translatable_json_string_display_field_renders_locale_string(): void
    {
        $field = HasManyField::make('tags', StubModel::class)
            ->withRelationName('tags')
            ->withDisplayField('name');

        // Display value stored as JSON string — the other translatable format
        $json = json_encode(['en' => 'Technology', 'uk' => 'Технологія']);
        $html = $this->renderFieldInput($field, [
            ['id' => 5, 'name' => $json],
        ]);

        self::assertStringContainsString('Technology', $html);
        self::assertStringNotContainsString('Array', $html);
    }

    private function renderFieldInput(mixed $field, mixed $value): string
    {
        return View::make('bpadmin::components.field-input', [
            'field'      => $field,
            'value'      => $value,
            'errors'     => new ViewErrorBag(),
            'definition' => $this->definition,
            'namePrefix' => null,
        ])->render();
    }
}
