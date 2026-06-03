<?php

declare(strict_types=1);

namespace BlackParadise\LaravelAdminBladeUI\Tests\Feature\Views;

use BlackParadise\CoreAdmin\Domain\Entity\EntityRecord;
use BlackParadise\LaravelAdmin\Core\EntityDefinitionRegistry;
use BlackParadise\LaravelAdminBladeUI\Http\Presenters\BladeEntityPresenter;
use BlackParadise\LaravelAdminBladeUI\Tests\Fixtures\StubJournalDefinition;
use BlackParadise\LaravelAdminBladeUI\Tests\Fixtures\StubJournalHistoryDefinition;
use BlackParadise\LaravelAdminBladeUI\Tests\TestCase;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Regression test for Bug #8: the edit form must render the hasMany-embed
 * section as a live repeater (bpRepeater + add/remove controls + template row),
 * NOT a static @foreach without those controls.
 *
 * Mirrors BladeEntityPresenterHasManyEmbedCreateTest but for edit.blade.php.
 */
final class BladeEntityPresenterHasManyEmbedEditTest extends TestCase
{
    private BladeEntityPresenter $presenter;
    private StubJournalDefinition $definition;

    protected function setUp(): void
    {
        parent::setUp();

        $this->presenter  = new BladeEntityPresenter();
        $this->definition = new StubJournalDefinition();

        $this->app->make(EntityDefinitionRegistry::class)
            ->register(new StubJournalHistoryDefinition());

        $request = Request::create('/admin/stub_journal/1/edit', 'GET');
        $request->setLaravelSession($this->app['session']->driver());
        $this->app->instance('request', $request);
    }

    public function test_edit_renders_has_many_embed_with_repeater_and_add_button(): void
    {
        $record = new EntityRecord(
            $this->definition,
            ['id' => 1, 'issn_print' => '1234-5678'],
            [
                'historyItems' => [
                    ['id' => 10, 'title' => 'First entry',  'period' => '2024-Q1'],
                    ['id' => 11, 'title' => 'Second entry', 'period' => '2024-Q2'],
                ],
            ],
        );

        $response = $this->presenter->edit($record, $this->definition->fields(), $this->definition);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        $body = (string) $response->getContent();

        // Must have Alpine repeater — not a plain static loop.
        self::assertStringContainsString('bpRepeater', $body);

        // Add-row control must be present.
        self::assertStringContainsString('data-bp-repeater-add', $body);

        // Template row for new items must carry index placeholder.
        self::assertStringContainsString('histories[__ROW__][title]', $body);

        // Existing rows must be pre-filled with their values.
        self::assertStringContainsString('First entry', $body);
        self::assertStringContainsString('Second entry', $body);

        // Remove button on each row.
        self::assertStringContainsString("closest('[data-bp-embed-row]').remove()", $body);
    }

    public function test_edit_renders_has_many_embed_with_zero_existing_items(): void
    {
        $record = new EntityRecord(
            $this->definition,
            ['id' => 2, 'issn_print' => '9999-0000'],
            ['historyItems' => []],
        );

        $response = $this->presenter->edit($record, $this->definition->fields(), $this->definition);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        $body = (string) $response->getContent();

        // Even with no existing items the repeater structure must be present.
        self::assertStringContainsString('bpRepeater', $body);
        self::assertStringContainsString('data-bp-repeater-add', $body);
        self::assertStringContainsString('histories[__ROW__][title]', $body);
    }
}
