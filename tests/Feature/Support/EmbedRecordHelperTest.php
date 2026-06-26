<?php

declare(strict_types=1);

namespace BlackParadise\LaravelAdminBladeUI\Tests\Feature\Support;

use BlackParadise\CoreAdmin\Domain\Entity\EntityRecord;
use BlackParadise\LaravelAdminBladeUI\Tests\Fixtures\StubJournalHistoryDefinition;
use BlackParadise\LaravelAdminBladeUI\Tests\TestCase;

final class EmbedRecordHelperTest extends TestCase
{
    public function test_wraps_flat_array_as_attributes_and_relations(): void
    {
        $def    = new StubJournalHistoryDefinition();
        $record = bp_embed_record($def, ['title' => 'X', 'events' => [['id' => 1, 'title' => 'E1']]]);

        self::assertInstanceOf(EntityRecord::class, $record);
        self::assertSame('X', $record->get('title'));                 // attributes
        self::assertSame([['id' => 1, 'title' => 'E1']], $record->relation('events')); // relations
    }
}
