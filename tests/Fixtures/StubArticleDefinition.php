<?php

declare(strict_types=1);

namespace BlackParadise\LaravelAdminBladeUI\Tests\Fixtures;

use BlackParadise\CoreAdmin\Domain\Fields\BelongsToField;
use BlackParadise\CoreAdmin\Domain\Fields\TextField;
use BlackParadise\LaravelAdmin\EntityDefinition;

/**
 * EntityDefinition fixture exercising a BelongsToField with a computed display
 * label (withDisplayUsing closure). Drives field-display.blade.php callback
 * branch for the belongs_to case.
 *
 * Target model is StubModel::class — tests do not query the DB; the display
 * callback reads the serialised relation array directly from EntityRecord.
 */
final class StubArticleDefinition extends EntityDefinition
{
    public string $model = StubModel::class;

    public function resolveName(): string
    {
        return 'stub_article';
    }

    public function fields(): array
    {
        return [
            TextField::make('title'),
            BelongsToField::make('journal_issue_id', StubModel::class)
                ->withRelationName('journalIssue')
                ->withDisplayField('number')
                ->withDisplayEagerLoad(['history'])
                ->withDisplayUsing(static function (array $row): string {
                    $rawHistory = $row['history'] ?? null;
                    $history    = is_array($rawHistory) ? $rawHistory : [];
                    $rawTitle   = $history['title'] ?? null;
                    $title      = is_array($rawTitle) ? $rawTitle : [];
                    $rawEn      = $title['en'] ?? null;
                    $en         = is_string($rawEn) ? $rawEn : '?';
                    $rawNumber  = $row['number'] ?? null;
                    $number     = is_string($rawNumber) || is_int($rawNumber) ? (string) $rawNumber : '';
                    return $en . ' — №' . $number;
                }),
        ];
    }
}
