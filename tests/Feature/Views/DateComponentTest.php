<?php

declare(strict_types=1);

namespace BlackParadise\LaravelAdminBladeUI\Tests\Feature\Views;

use BlackParadise\CoreAdmin\Domain\Fields\DateField;
use BlackParadise\LaravelAdminBladeUI\Tests\TestCase;

/**
 * Bug #13 — the date component must never throw while rendering a rejected
 * old() value. <input type=date> can only display Y-m-d, so unparsable input
 * falls back to an empty value attribute instead of crashing (500).
 */
final class DateComponentTest extends TestCase
{
    private function render(mixed $value): string
    {
        return view('bpadmin::components.field.date', [
            'field' => DateField::make('published_on'),
            'value' => $value,
        ])->render();
    }

    public function test_invalid_value_renders_without_throwing(): void
    {
        $html = $this->render('not-a-date');

        self::assertStringContainsString('type="date"', $html);
        self::assertStringContainsString('value=""', $html);
    }

    public function test_valid_value_is_formatted_to_ymd(): void
    {
        $html = $this->render('2026-06-06 12:34:56');

        self::assertStringContainsString('value="2026-06-06"', $html);
    }

    public function test_empty_value_renders_empty(): void
    {
        $html = $this->render(null);

        self::assertStringContainsString('value=""', $html);
    }
}
