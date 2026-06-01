<?php

declare(strict_types=1);

namespace BlackParadise\LaravelAdminBladeUI\Tests\Feature\Views;

use BlackParadise\LaravelAdmin\Core\EntityDefinitionRegistry;
use BlackParadise\LaravelAdminBladeUI\Http\Presenters\BladeEntityPresenter;
use BlackParadise\LaravelAdminBladeUI\Tests\Fixtures\StubJournalDefinition;
use BlackParadise\LaravelAdminBladeUI\Tests\Fixtures\StubJournalHistoryDefinition;
use BlackParadise\LaravelAdminBladeUI\Tests\TestCase;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Regression test for ADMIN-V3-BUGS #1: the create form must render a
 * hasMany-embed relation as an indexed repeater so the submitted payload is a
 * LIST (histories[0][title], histories[1][title]…). The old create view
 * rendered child fields flat (histories[title]), producing an associative
 * object that RelationWriter::applyChildSync skips — children were silently
 * dropped on create.
 */
final class BladeEntityPresenterHasManyEmbedCreateTest extends TestCase
{
    private BladeEntityPresenter $presenter;
    private StubJournalDefinition $definition;

    protected function setUp(): void
    {
        parent::setUp();

        $this->presenter = new BladeEntityPresenter();
        $this->definition = new StubJournalDefinition();

        // create.blade.php resolves the embedded definition through the registry.
        $this->app->make(EntityDefinitionRegistry::class)
            ->register(new StubJournalHistoryDefinition());

        $request = Request::create('/admin/stub_journal/create', 'GET');
        $request->setLaravelSession($this->app['session']->driver());
        $this->app->instance('request', $request);
    }

    public function test_create_renders_has_many_embed_as_indexed_repeater(): void
    {
        $response = $this->presenter->create($this->definition->fields(), $this->definition);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        $body = (string) $response->getContent();

        // The bug: child fields rendered flat as an associative object.
        self::assertStringNotContainsString('name="histories[period]"', $body);
        self::assertStringNotContainsString('name="histories[title]"', $body);

        // The fix: an indexed repeater (Alpine bpRepeater) with a blank-row
        // <template> whose child names carry an index placeholder, plus an
        // "add row" control.
        self::assertStringContainsString('bpRepeater', $body);
        self::assertStringContainsString('data-bp-repeater-add', $body);
        self::assertStringContainsString('histories[__ROW__][period]', $body);
    }
}
