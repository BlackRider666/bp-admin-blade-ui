<?php

declare(strict_types=1);

namespace BlackParadise\LaravelAdminBladeUI\Tests\Feature\Views;

use BlackParadise\CoreAdmin\Domain\Fields\BelongsToField;
use BlackParadise\CoreAdmin\Domain\Fields\BooleanField;
use BlackParadise\CoreAdmin\Domain\Fields\EditorField;
use BlackParadise\CoreAdmin\Domain\Fields\TextField;
use BlackParadise\LaravelAdminBladeUI\Tests\Fixtures\StubModel;
use BlackParadise\LaravelAdminBladeUI\Tests\TestCase;
use Illuminate\Support\Facades\View;

/**
 * RED tests for A20–A30: blade component render-assertions.
 *
 * These tests use view()->render() directly rather than BladeEntityPresenter
 * so they run independently of the A15 fatal (missing actionResult() method).
 * Each test asserts the POST-FIX correct behaviour — all tests are RED now
 * because the bugs still exist in the current source.
 */
final class BugFixBladeComponentsTest extends TestCase
{
    // ------------------------------------------------------------------
    // A20 (B7): belongs-to search filters via Alpine x-for / filteredOptions
    // ------------------------------------------------------------------

    /**
     * A20: rendered belongs-to component must use Alpine x-for over filteredOptions,
     * NOT a static PHP @forelse loop. Currently the component uses @forelse — RED.
     */
    public function test_a20_belongs_to_component_renders_alpine_x_for_over_filtered_options(): void
    {
        $field = BelongsToField::make('country_id', StubModel::class)
            ->withRelationName('country')
            ->withDisplayField('name');

        $field->withMeta([
            'options' => [
                ['id' => 1, 'label' => 'Ukraine'],
                ['id' => 2, 'label' => 'Poland'],
            ],
        ]);

        $html = view('bpadmin::components.field.belongs-to', [
            'field' => $field,
            'value' => null,
            'name'  => 'country_id',
        ])->render();

        // Post-fix: options are driven by Alpine filteredOptions, not @forelse.
        self::assertStringContainsString('filteredOptions', $html);
        self::assertStringContainsString('x-for', $html);

        // Post-fix: NO static server-side @forelse iteration in the dropdown.
        // (The bug: options are hardcoded via @forelse; search doesn't filter them.)
        self::assertStringNotContainsString('@forelse', $html);
    }

    // ------------------------------------------------------------------
    // A21 (B8): editor component uses Alpine x-init, NOT inline <script>,
    //           and id is derived from the input name, NOT uniqid().
    // ------------------------------------------------------------------

    /**
     * A21: editor component must NOT contain server-side uniqid() output,
     * must derive its id from the input name, and must use Alpine x-init
     * rather than an inline <script> tag that fails on innerHTML cloning.
     * Currently the component uses uniqid() + inline <script> — RED.
     */
    public function test_a21_editor_component_does_not_contain_uniqid_and_uses_alpine_x_init(): void
    {
        $field = EditorField::make('body');

        $html = view('bpadmin::components.field.editor', [
            'field' => $field,
            'value' => null,
            'name'  => 'body',
        ])->render();

        // Post-fix: id must be deterministic, derived from the input name.
        // "quill-body" is the expected pattern (preg_replace non-alphanumeric → '-').
        self::assertStringContainsString('quill-body', $html);

        // Post-fix: no inline <script> tags — these fail in innerHTML-cloned rows.
        self::assertStringNotContainsString('<script>', $html);

        // Post-fix: Alpine x-init drives initialisation so it works in repeater clones.
        self::assertStringContainsString('x-init', $html);

        // Post-fix: no server-generated uniqid() randomness in the rendered output.
        // The bug: id="quill-body-6659ab3f4bde2.12345678" (has dots/random suffix).
        // We assert there is NO dot-separated random suffix after the field name.
        self::assertMatchesRegularExpression('/id="quill-body(?:-input)?"/', $html);
    }

    // ------------------------------------------------------------------
    // A22 (B9): field-input errorKey uses Laravel dot-notation for
    //           bracket-style embedded names (histories[0][category]).
    // ------------------------------------------------------------------

    /**
     * A22: field-input with namePrefix='histories[0]' and field 'category'
     * must produce input name="histories[0][category]" BUT errorKey in
     * Laravel dot-notation: "histories.0.category".
     * Currently errorKey = "histories[0].category" (mixed) — RED.
     *
     * We inject an actual error under the POST-FIX dotted key so the @error
     * directive renders the error message — that's how we confirm which key
     * the template uses.
     */
    public function test_a22_embedded_field_input_error_key_uses_dot_notation(): void
    {
        $field = TextField::make('category')->required();

        // Inject an error under the expected post-fix dotted key.
        $errors = new \Illuminate\Support\ViewErrorBag();
        $errors = $errors->put('default', new \Illuminate\Support\MessageBag([
            'histories.0.category' => ['Category is required.'],
        ]));
        View::share('errors', $errors);

        $html = view('bpadmin::components.field-input', [
            'field'      => $field,
            'value'      => null,
            'errors'     => $errors,
            'definition' => null,
            'namePrefix' => 'histories[0]',
        ])->render();

        // Input name must use bracket notation.
        self::assertStringContainsString('name="histories[0][category]"', $html);

        // Post-fix: @error uses dot-notation key histories.0.category, so the
        // injected error message is rendered.
        self::assertStringContainsString('Category is required.', $html);

        // Bug: current code uses $namePrefix.'.'.$field->name() = 'histories[0].category'
        // (bracket prefix + dot separator) — @error('histories[0].category') would match
        // a key that has literal brackets, not the Laravel-generated 'histories.0.category'.
    }

    // ------------------------------------------------------------------
    // A23 (B21b): embedded belongsTo errorKey uses dot-notation
    // ------------------------------------------------------------------

    /**
     * A23: field-input with namePrefix='publication_id' and a belongsTo field 'title'
     * must produce errorKey "publication_id.title" (dot) so @error() catches Laravel's
     * dotted validation keys.
     *
     * We inject an error under the post-fix key 'publication_id.title' and verify
     * it renders — confirming the template uses the correct dot-notation errorKey.
     *
     * NOTE: With a plain (non-bracketed) namePrefix like 'publication_id', the current
     * code ALREADY produces errorKey = 'publication_id.title' via
     * $namePrefix . '.' . $field->name(). So this test is PRE-GREEN for the current
     * code. Declared as --pre-green in bp-red. Justified: the fix matters only for
     * bracket-indexed prefixes like 'histories[0]' (A22). This test documents the
     * expected behaviour for the simpler case to prevent regression.
     */
    public function test_a23_embedded_belongs_to_field_input_error_key_uses_dot_notation(): void
    {
        $field = TextField::make('title')->required();

        // Inject error under the expected dotted key.
        $errors = new \Illuminate\Support\ViewErrorBag();
        $errors = $errors->put('default', new \Illuminate\Support\MessageBag([
            'publication_id.title' => ['Title is required.'],
        ]));
        View::share('errors', $errors);

        $html = view('bpadmin::components.field-input', [
            'field'      => $field,
            'value'      => null,
            'errors'     => $errors,
            'definition' => null,
            'namePrefix' => 'publication_id',
        ])->render();

        // Input name: publication_id[title].
        self::assertStringContainsString('name="publication_id[title]"', $html);

        // Error rendered via dot-notation key.
        self::assertStringContainsString('Title is required.', $html);
    }

    // ------------------------------------------------------------------
    // A24 (V1): boolean has id on button; composite labels have no broken for=
    // ------------------------------------------------------------------

    /**
     * A24a: boolean.blade.php must render a <button> with an id attribute
     * so the enclosing <label for="..."> in field-input.blade.php resolves correctly.
     * Currently the boolean button has no id — RED.
     */
    public function test_a24_boolean_component_renders_button_with_id(): void
    {
        $field = BooleanField::make('is_active');

        $html = view('bpadmin::components.field.boolean', [
            'field' => $field,
            'value' => false,
            'name'  => 'is_active',
        ])->render();

        // Post-fix: the button has id="is_active".
        self::assertStringContainsString('id="is_active"', $html);
        self::assertStringContainsString('<button', $html);
    }

    /**
     * A24b: for composite types (editor, translatable) the outer <label> in
     * field-input.blade.php must NOT have a `for` attribute (no broken reference).
     * Currently it always emits for="..." even for composite controls — RED.
     */
    public function test_a24_editor_field_input_label_has_no_for_attribute(): void
    {
        $field = EditorField::make('body');

        $html = view('bpadmin::components.field-input', [
            'field'      => $field,
            'value'      => null,
            'errors'     => new \Illuminate\Support\ViewErrorBag(),
            'definition' => null,
            'namePrefix' => null,
        ])->render();

        // Post-fix: label must NOT have for= on composite types.
        // Bug: label always has for="body" even when the actual control is a Quill div.
        self::assertStringNotContainsString('<label for="body"', $html);
        // But label text must still render.
        self::assertStringContainsString('<label', $html);
    }

    // ------------------------------------------------------------------
    // A25 (V2): locale-switcher dropdown uses bg-bp-surface, NOT bg-white
    // ------------------------------------------------------------------

    /**
     * A25: locale-switcher dropdown panel must use bg-bp-surface (theme token)
     * so it adapts to dark mode. Currently it uses hardcoded bg-white — RED.
     */
    public function test_a25_locale_switcher_dropdown_uses_theme_surface_token(): void
    {
        // Set up two locales so the switcher renders (needs > 1 locale).
        config()->set('bpadmin.locales', ['en', 'uk']);

        $html = view('bpadmin::components.locale-switcher')->render();

        // Post-fix: uses theme token.
        self::assertStringContainsString('bg-bp-surface', $html);

        // Bug: hardcoded white.
        self::assertStringNotContainsString('bg-white', $html);
    }

    // ------------------------------------------------------------------
    // A26 (V3): layout/app <html lang> reflects app()->getLocale()
    // ------------------------------------------------------------------

    /**
     * A26: layout/app.blade.php must emit <html lang="uk"> when the app locale is uk.
     * Currently it has hardcoded lang="en" — RED.
     */
    public function test_a26_layout_app_html_lang_reflects_current_locale(): void
    {
        $this->app->setLocale('uk');

        $html = view('bpadmin::layout.app')->render();

        // Post-fix: dynamic locale in html[lang].
        self::assertStringContainsString('<html lang="uk"', $html);

        // Bug: hardcoded en regardless of locale.
        self::assertStringNotContainsString('<html lang="en"', $html);
    }
}
