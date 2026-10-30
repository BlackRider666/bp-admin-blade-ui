<?php

declare(strict_types=1);

namespace BlackParadise\LaravelAdminBladeUI\Tests\Feature\Views;

use BlackParadise\CoreAdmin\Domain\Fields\FileField;
use BlackParadise\CoreAdmin\Domain\Fields\ImageField;
use BlackParadise\CoreAdmin\Domain\Fields\MorphFileField;
use BlackParadise\LaravelAdminBladeUI\Tests\TestCase;

/**
 * A13 / A14 / A53: the remove-file checkbox `{field}__clear` is rendered only for OPTIONAL file fields
 * (no `required` rule, not requiredOnCreate) that currently have a stored file.
 */
final class RemoveFileCheckboxTest extends TestCase
{
    private function renderFile(FileField $field, ?string $value = 'docs/a.pdf'): string
    {
        return view('bpadmin::components.field.file', ['field' => $field, 'value' => $value, 'name' => $field->name()])->render();
    }

    public function test_file_optional_field_with_stored_file_renders_clear_checkbox(): void
    {
        $html = $this->renderFile(FileField::make('doc'));

        self::assertMatchesRegularExpression('/<input[^>]*type="checkbox"[^>]*name="doc__clear"/', $html);
    }

    public function test_file_clear_checkbox_submits_value_one(): void
    {
        $html = $this->renderFile(FileField::make('doc'));

        self::assertMatchesRegularExpression('/<input[^>]*name="doc__clear"[^>]*value="1"/', $html);
    }

    public function test_file_required_field_renders_no_clear_checkbox(): void
    {
        $html = $this->renderFile(FileField::make('doc')->required());

        self::assertStringNotContainsString('doc__clear', $html);
    }

    public function test_file_required_on_create_field_renders_no_clear_checkbox(): void
    {
        $html = $this->renderFile(FileField::make('doc')->requiredOnCreate());

        self::assertStringNotContainsString('doc__clear', $html);
    }

    public function test_file_optional_field_without_stored_file_renders_no_clear_checkbox(): void
    {
        $html = $this->renderFile(FileField::make('doc'), null);

        self::assertStringNotContainsString('doc__clear', $html);
    }

    public function test_image_optional_field_with_stored_file_renders_clear_checkbox(): void
    {
        $html = view('bpadmin::components.field.image', [
            'field' => ImageField::make('cover'), 'value' => 'img/c.png', 'name' => 'cover',
        ])->render();

        self::assertMatchesRegularExpression('/<input[^>]*type="checkbox"[^>]*name="cover__clear"/', $html);
    }

    public function test_image_required_field_renders_no_clear_checkbox(): void
    {
        $html = view('bpadmin::components.field.image', [
            'field' => ImageField::make('cover')->required(), 'value' => 'img/c.png', 'name' => 'cover',
        ])->render();

        self::assertStringNotContainsString('cover__clear', $html);
    }

    public function test_morph_file_optional_field_with_attachment_renders_clear_checkbox(): void
    {
        $html = view('bpadmin::components.field.morph-file', [
            'field' => MorphFileField::make('attachment'),
            'value' => ['path' => 'files/a.pdf', 'name' => 'a.pdf', 'mime_type' => 'application/pdf'],
            'name'  => 'attachment',
        ])->render();

        self::assertMatchesRegularExpression('/<input[^>]*type="checkbox"[^>]*name="attachment__clear"/', $html);
    }

    public function test_morph_file_required_field_renders_no_clear_checkbox(): void
    {
        $html = view('bpadmin::components.field.morph-file', [
            'field' => MorphFileField::make('attachment')->required(),
            'value' => ['path' => 'files/a.pdf', 'name' => 'a.pdf', 'mime_type' => 'application/pdf'],
            'name'  => 'attachment',
        ])->render();

        self::assertStringNotContainsString('attachment__clear', $html);
    }

    public function test_morph_file_required_on_create_field_renders_no_clear_checkbox(): void
    {
        $html = view('bpadmin::components.field.morph-file', [
            'field' => MorphFileField::make('attachment')->requiredOnCreate(),
            'value' => ['path' => 'files/a.pdf', 'name' => 'a.pdf', 'mime_type' => 'application/pdf'],
            'name'  => 'attachment',
        ])->render();

        self::assertStringNotContainsString('attachment__clear', $html);
    }
}
