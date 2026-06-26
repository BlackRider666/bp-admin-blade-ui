<?php

declare(strict_types=1);

namespace BlackParadise\LaravelAdminBladeUI\Tests\Feature\Views;

use BlackParadise\CoreAdmin\Domain\Entity\EntityRecord;
use BlackParadise\LaravelAdmin\Core\EntityDefinitionRegistry;
use BlackParadise\LaravelAdminBladeUI\Http\Presenters\BladeEntityPresenter;
use BlackParadise\LaravelAdminBladeUI\Tests\Fixtures\StubJournalDefinition;
use BlackParadise\LaravelAdminBladeUI\Tests\Fixtures\StubJournalHistoryDefinition;
use BlackParadise\LaravelAdminBladeUI\Tests\TestCase;
use Symfony\Component\HttpFoundation\Response;

final class BladeEntityPresenterEmbedShowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->app->make(EntityDefinitionRegistry::class)
            ->register(new StubJournalHistoryDefinition());
    }

    public function test_show_renders_embedded_belongs_to_many_value(): void
    {
        $definition = new StubJournalDefinition();
        $record = new EntityRecord(
            $definition,
            ['id' => 1, 'issn_print' => '1234-5678'],
            [
                // embedded hasMany 'historyItems', кожен несе вкладений belongsToMany 'events'
                'historyItems' => [[
                    'id' => 10, 'title' => 'First entry', 'period' => '2024-Q1',
                    'events' => [['id' => 7, 'title' => 'Conf Alpha']],
                ]],
            ],
        );

        $presenter = new BladeEntityPresenter();
        $response  = $presenter->show($record, $definition->fields(), $definition);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        $body = (string) $response->getContent();
        self::assertStringContainsString('First entry', $body);  // скалярне sub-поле
        self::assertStringContainsString('Conf Alpha', $body);   // вкладений belongsToMany — чіп
    }
}
