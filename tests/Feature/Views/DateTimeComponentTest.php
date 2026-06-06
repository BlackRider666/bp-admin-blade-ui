<?php

declare(strict_types=1);

namespace BlackParadise\LaravelAdminBladeUI\Tests\Feature\Views;

use BlackParadise\CoreAdmin\Domain\Fields\DateTimeField;
use BlackParadise\LaravelAdminBladeUI\Tests\TestCase;

/**
 * Bug #13 (datetime twin) — the datetime component must never throw while
 * rendering a rejected old() value. <input type=datetime-local> can only display
 * Y-m-d\TH:i, so unparsable input falls back to an empty value instead of 500.
 */
final class DateTimeComponentTest extends TestCase
{
    private function render(mixed $value): string
    {
        return view('bpadmin::components.field.datetime', [
            'field' => DateTimeField::make('published_at'),
            'value' => $value,
        ])->render();
    }

    public function test_invalid_value_renders_without_throwing(): void
    {
        $html = $this->render('not-a-date');

        self::assertStringContainsString('type="datetime-local"', $html);
        self::assertStringContainsString('value=""', $html);
    }

    public function test_valid_value_is_formatted(): void
    {
        $html = $this->render('2026-06-06 12:34:56');

        self::assertStringContainsString('value="2026-06-06T12:34"', $html);
    }

    public function test_empty_value_renders_empty(): void
    {
        $html = $this->render(null);

        self::assertStringContainsString('value=""', $html);
    }
}
