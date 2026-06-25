<?php

declare(strict_types=1);

namespace BlackParadise\LaravelAdminBladeUI\Tests\Feature;

use BlackParadise\CoreAdmin\Domain\Query\PaginatedResult;
use BlackParadise\LaravelAdminBladeUI\Http\Presenters\BladeEntityPresenter;
use BlackParadise\LaravelAdminBladeUI\Tests\Fixtures\StubEditorDefinition;
use BlackParadise\LaravelAdminBladeUI\Tests\Fixtures\StubMixedEditorDefinition;
use BlackParadise\LaravelAdminBladeUI\Tests\TestCase;
use Illuminate\Http\Request;

/**
 * Verifies that Quill rich-text editor assets are loaded conditionally:
 * - absent on list/index pages (no editor field rendered)
 * - present exactly once with `defer` on form pages that contain an editor field
 * - present exactly once even when BOTH editor and translatable-editor are on the same page
 *
 * After the fix: Quill CSS+JS are pushed via @push('bp-head') inside
 * editor.blade.php / translatable.blade.php (wrapped in @once('bpadmin-quill-assets')),
 * and @stack('bp-head') sits in app.blade.php before the deferred bpadmin.js tag.
 */
final class QuillConditionalLoadTest extends TestCase
{
    private BladeEntityPresenter $presenter;
    private StubEditorDefinition $definition;

    protected function setUp(): void
    {
        parent::setUp();

        $this->presenter = new BladeEntityPresenter();
        $this->definition = new StubEditorDefinition();

        $request = Request::create('/admin/stub_editor_entity/create', 'GET');
        $request->setLaravelSession($this->app['session']->driver());
        $this->app->instance('request', $request);
    }

    public function test_list_page_does_not_load_quill_assets(): void
    {
        $paginated = new PaginatedResult(items: [], total: 0, page: 1, perPage: 15);

        $response = $this->presenter->index($paginated, $this->definition->fields(), $this->definition);
        $html = (string) $response->getContent();

        // Assert on CDN URL rather than bare 'quill' — avoids false positives from
        // editor element ids like id="quill-body" that appear even on list pages.
        self::assertStringNotContainsString('cdn.jsdelivr.net/npm/quill', $html);
    }

    public function test_form_page_with_editor_field_loads_quill_exactly_once_with_defer(): void
    {
        $response = $this->presenter->create($this->definition->fields(), $this->definition);
        $html = (string) $response->getContent();

        self::assertSame(1, substr_count($html, 'quill.js'));
        self::assertMatchesRegularExpression('/<script[^>]+quill\.js[^>]*defer/', $html);

        // SRI integrity and CSS dedup (FINDING 2)
        self::assertSame(1, substr_count($html, 'quill.snow.css'));
        self::assertStringContainsString(
            'integrity="sha384-utBUCeG4SYaCm4m7GQZYr8Hy8Fpy3V4KGjBZaf4WTKOcwhCYpt/0PfeEe3HNlwx8"',
            $html,
            'Quill JS SRI integrity hash must be present'
        );
        self::assertStringContainsString(
            'integrity="sha384-ecIckRi4QlKYya/FQUbBUjS4qp65jF/J87Guw5uzTbO1C1Jfa/6kYmd6dXUF6D7i"',
            $html,
            'Quill CSS SRI integrity hash must be present'
        );
    }

    /**
     * FINDING 1 regression test: bare @once uses UUID-per-file → two components
     * each add their own @push block, loading Quill twice.
     * Fixed by @once('bpadmin-quill-assets') shared key in both components.
     */
    public function test_form_page_with_both_editor_and_translatable_editor_loads_quill_exactly_once(): void
    {
        $definition = new StubMixedEditorDefinition();

        $request = Request::create('/admin/stub_mixed_editor_entity/create', 'GET');
        $request->setLaravelSession($this->app['session']->driver());
        $this->app->instance('request', $request);

        $response = $this->presenter->create($definition->fields(), $definition);
        $html = (string) $response->getContent();

        self::assertSame(
            1,
            substr_count($html, 'quill.js'),
            'Quill JS must appear exactly once even when both editor and translatable-editor fields are on the same page'
        );
    }
}
