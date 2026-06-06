<?php

declare(strict_types=1);

namespace BlackParadise\LaravelAdminBladeUI\Tests\Feature\Views;

use BlackParadise\CoreAdmin\Domain\Fields\BelongsToField;
use BlackParadise\CoreAdmin\Domain\Fields\TranslatableField;
use BlackParadise\LaravelAdminBladeUI\Tests\Fixtures\StubModel;
use BlackParadise\LaravelAdminBladeUI\Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

/**
 * RED follow-up tests for adversarial-review gaps in BugFixBladeComponentsTest.
 *
 * Each method asserts the POST-FIX correct behaviour for defects that the first
 * round of render-assertion tests missed. All methods must be RED against the
 * current (unfixed) source code.
 *
 * Gaps covered:
 *  - A21a: translatable editor branch uses inline <script>/new Quill instead of Alpine bpQuill/x-init
 *  - A21b: translatable editor id is derived from field->name() (shared per-row) not from $inputName (unique per-row)
 *  - A22:  translatable editor branch ignores old() — value not restored after validation error
 *  - A24:  belongs-to label for= points to $htmlName but the control id is $htmlName-search (mismatch)
 */
final class BugFixBladeComponentsFollowupTest extends TestCase
{
    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    /**
     * Boot a request with a session so old() / session helpers work in views.
     * Returns the session driver so callers can flash additional old input.
     */
    private function bootRequestWithSession(): \Illuminate\Session\Store
    {
        $session = $this->app['session']->driver();
        $request = Request::create('/admin/test', 'GET');
        $request->setLaravelSession($session);
        $this->app->instance('request', $request);

        return $session;
    }

    // ------------------------------------------------------------------
    // A21a: translatable editor branch — NO inline <script>, uses Alpine x-init / bpQuill
    // ------------------------------------------------------------------

    /**
     * A21a: the editor branch of translatable.blade.php must NOT emit an inline
     * <script> containing `new Quill(...)`. Instead it must use the Alpine `bpQuill`
     * component / `x-init` mechanism, exactly as the standalone editor.blade.php does.
     *
     * Currently translatable.blade.php lines 43-72 contain a raw <script>(function(){...
     * new Quill(container, {...}) ... }()); — RED.
     */
    public function test_a21a_translatable_editor_branch_does_not_emit_inline_script(): void
    {
        $this->bootRequestWithSession();

        config()->set('bpadmin.locales', ['en']);
        config()->set('app.locale', 'en');

        $field = TranslatableField::make('body')->asEditor();

        $html = view('bpadmin::components.field.translatable', [
            'field'    => $field,
            'value'    => null,
            'name'     => 'body',
            'errorKey' => null,
        ])->render();

        // Post-fix: NO inline <script> tag in the editor branch.
        self::assertStringNotContainsString('<script', $html);

        // Post-fix: NO raw `new Quill` instantiation in server-rendered output.
        self::assertStringNotContainsString('new Quill', $html);

        // Post-fix: Alpine bpQuill component drives initialisation (mirrors editor.blade.php).
        self::assertStringContainsString('bpQuill', $html);

        // Post-fix: Alpine x-init wires the editor to its container id.
        self::assertStringContainsString('x-init', $html);
    }

    // ------------------------------------------------------------------
    // A21b: translatable editor id must be derived from $inputName (per-row unique)
    //        not from $field->name() (shared across all repeater rows)
    // ------------------------------------------------------------------

    /**
     * A21b: when the translatable component is rendered with two different
     * namePrefix values that produce different $inputName values for the same
     * logical field, the generated editor/control id must differ per rendering.
     *
     * Currently the component derives $componentId from `$field->name()` via
     * `md5($field->name())` — the same for every repeater row. This causes
     * duplicate HTML ids and broken Quill init on rows 1+.
     *
     * Post-fix: $componentId (and therefore $editorId) must be derived from
     * $inputName (the full bracket-prefixed name), so rows differ.
     *
     * Two renders with name='histories[0][title]' and name='histories[1][title]'
     * must produce different editor ids.
     */
    public function test_a21b_translatable_editor_id_is_unique_per_row_name(): void
    {
        $this->bootRequestWithSession();

        config()->set('bpadmin.locales', ['en']);
        config()->set('app.locale', 'en');

        $field = TranslatableField::make('title')->asEditor();

        // Row 0.
        $htmlRow0 = view('bpadmin::components.field.translatable', [
            'field'    => $field,
            'value'    => null,
            'name'     => 'histories[0][title]',
            'errorKey' => null,
        ])->render();

        // Row 1.
        $htmlRow1 = view('bpadmin::components.field.translatable', [
            'field'    => $field,
            'value'    => null,
            'name'     => 'histories[1][title]',
            'errorKey' => null,
        ])->render();

        // Extract all id="..." values from each render.
        preg_match_all('/\bid="([^"]+)"/', $htmlRow0, $matchesRow0);
        preg_match_all('/\bid="([^"]+)"/', $htmlRow1, $matchesRow1);

        $idsRow0 = $matchesRow0[1];
        $idsRow1 = $matchesRow1[1];

        self::assertNotEmpty($idsRow0, 'Row 0 render must emit at least one id="..." attribute');
        self::assertNotEmpty($idsRow1, 'Row 1 render must emit at least one id="..." attribute');

        // Post-fix: the ids for different input names MUST differ (name-derived, not field-name-derived).
        // If any id from row0 appears identically in row1, the component still uses field->name().
        $intersection = array_intersect($idsRow0, $idsRow1);

        self::assertEmpty(
            $intersection,
            sprintf(
                'Editor ids must be unique per row (derived from $inputName), '
                . 'but rows 0 and 1 share id(s): %s — '
                . 'this means $componentId still uses $field->name(), not $inputName.',
                implode(', ', $intersection),
            ),
        );
    }

    // ------------------------------------------------------------------
    // A22: translatable editor branch must restore value from old() after validation error
    // ------------------------------------------------------------------

    /**
     * A22: the editor branch of translatable.blade.php sets
     * `value="{{ $translations[$locale] ?? '' }}"` — it does NOT call `old()`.
     *
     * After a validation error Laravel flashes the submitted input into the session
     * under the dotted key (e.g. `histories.0.title.en`). When the form re-renders,
     * the editor branch must seed its hidden input from `old($errorKey.'.'.$locale, ...)`
     * so the user's typed content is preserved.
     *
     * Currently the editor branch ignores old() entirely — RED.
     *
     * The test flashes old input for key 'body.en' and verifies it appears in the
     * rendered output of the hidden input's value attribute.
     */
    public function test_a22_translatable_editor_branch_restores_value_from_old_input(): void
    {
        config()->set('bpadmin.locales', ['en']);
        config()->set('app.locale', 'en');

        $session = $this->bootRequestWithSession();

        // Flash the submitted (old) input value as Laravel would after a validation error.
        // The key is the dot-notation error key: fieldName.locale
        $session->flashInput(['body.en' => 'Flashed editor content']);

        $field = TranslatableField::make('body')->asEditor();

        $html = view('bpadmin::components.field.translatable', [
            'field'    => $field,
            'value'    => ['en' => 'Original model content'],
            'name'     => 'body',
            'errorKey' => null,
        ])->render();

        // Post-fix: the flashed value must appear in the rendered output.
        // (The model's 'Original model content' must NOT take precedence over old().)
        self::assertStringContainsString('Flashed editor content', $html);

        // The model value must not override the flashed input.
        self::assertStringNotContainsString('Original model content', $html);
    }

    // ------------------------------------------------------------------
    // A24: belongs-to label for= must match the focusable control id
    // ------------------------------------------------------------------

    /**
     * A24: field-input.blade.php emits `<label for="{{ $htmlName }}">` for belongs-to
     * (not a composite type), but the belongs-to component renders its focusable
     * search input with `id="{{ $inputName }}-search"` (with -search suffix).
     *
     * This means `for="country_id"` points to nothing — the actual focusable control
     * is `id="country_id-search"`. A screen reader / clicking the label will not focus
     * the control.
     *
     * Post-fix: either the label `for` must match the control id exactly
     * (`for="country_id-search"`) OR the focusable control must have `id="country_id"`
     * (without suffix). Either way, `<label for="X">` must have a matching `id="X"`.
     *
     * Currently the for/id pair is mismatched — RED.
     */
    public function test_a24_belongs_to_label_for_matches_focusable_control_id(): void
    {
        $this->bootRequestWithSession();

        $field = BelongsToField::make('country_id', StubModel::class)
            ->withRelationName('country')
            ->withDisplayField('name');

        $field->withMeta([
            'options' => [
                ['id' => 1, 'label' => 'Ukraine'],
            ],
        ]);

        $html = view('bpadmin::components.field-input', [
            'field'      => $field,
            'value'      => null,
            'errors'     => new \Illuminate\Support\ViewErrorBag(),
            'definition' => null,
            'namePrefix' => null,
        ])->render();

        // Extract all label for="..." values.
        preg_match_all('/<label[^>]+for="([^"]+)"/', $html, $labelMatches);
        $labelFors = $labelMatches[1];

        // Extract all id="..." values.
        preg_match_all('/\bid="([^"]+)"/', $html, $idMatches);
        $allIds = $idMatches[1];

        // There must be a label with a `for` attribute.
        self::assertNotEmpty(
            $labelFors,
            'field-input must render a <label for="..."> for belongs-to fields',
        );

        // Post-fix: every label for="X" must have a matching id="X" in the rendered output.
        foreach ($labelFors as $forValue) {
            self::assertContains(
                $forValue,
                $allIds,
                sprintf(
                    '<label for="%s"> has no matching id="%s" in the output — '
                    . 'the belongs-to focusable control id is "%s-search" but the label points to "%s".',
                    $forValue,
                    $forValue,
                    $forValue,
                    $forValue,
                ),
            );
        }
    }
}
