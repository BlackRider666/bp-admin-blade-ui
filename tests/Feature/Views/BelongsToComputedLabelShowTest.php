<?php

declare(strict_types=1);

namespace BlackParadise\LaravelAdminBladeUI\Tests\Feature\Views;

use BlackParadise\CoreAdmin\Domain\Entity\EntityRecord;
use BlackParadise\LaravelAdminBladeUI\Http\Presenters\BladeEntityPresenter;
use BlackParadise\LaravelAdminBladeUI\Tests\Fixtures\StubArticleDefinition;
use BlackParadise\LaravelAdminBladeUI\Tests\TestCase;
use Symfony\Component\HttpFoundation\Response;

final class BelongsToComputedLabelShowTest extends TestCase
{
    public function test_show_uses_computed_label_for_belongs_to(): void
    {
        $definition = new StubArticleDefinition();
        $record = new EntityRecord(
            $definition,
            ['id' => 1, 'journal_issue_id' => 9],
            [
                // deep-серіалізований journalIssue несе number + вкладений history.title
                'journalIssue' => [
                    'id' => 9, 'number' => 9,
                    'history' => ['id' => 3, 'title' => ['en' => 'World Studies']],
                ],
            ],
        );

        $presenter = new BladeEntityPresenter();
        $response  = $presenter->show($record, $definition->fields(), $definition);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        $body = (string) $response->getContent();
        self::assertStringContainsString('World Studies — №9', $body);
    }
}
