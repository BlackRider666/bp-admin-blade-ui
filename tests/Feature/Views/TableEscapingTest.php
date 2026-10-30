<?php

declare(strict_types=1);

namespace BlackParadise\LaravelAdminBladeUI\Tests\Feature\Views;

use BlackParadise\CoreAdmin\Domain\Contracts\Auth\AuthorizationProviderContract;
use BlackParadise\CoreAdmin\Domain\Entity\EntityRecord;
use BlackParadise\CoreAdmin\Domain\Query\PaginatedResult;
use BlackParadise\LaravelAdminBladeUI\Http\Presenters\BladeEntityPresenter;
use BlackParadise\LaravelAdminBladeUI\Tests\Feature\Views\Concerns\RendersUiPages;
use BlackParadise\LaravelAdminBladeUI\Tests\Fixtures\FakeAuthorization;
use BlackParadise\LaravelAdminBladeUI\Tests\Fixtures\UiStateDefinition;
use BlackParadise\LaravelAdminBladeUI\Tests\TestCase;

/**
 * Task 5.2 fix1: m-2 (record key interpolated into Alpine/JS expressions must be JS-encoded)
 * and m-3 (actions column header translated via bpadmin::common.actions).
 */
final class TableEscapingTest extends TestCase
{
    use RendersUiPages;

    private function renderWithKey(string $key): string
    {
        $definition = new UiStateDefinition();
        $record = new EntityRecord($definition, ['id' => $key, 'title' => 'Hello']);
        $response = $this->presenterWith(new FakeAuthorization(['*']))->index(
            new PaginatedResult([$record], 1, 1, 15),
            $definition->fields(),
            $definition,
        );

        return (string) $response->getContent();
    }

    public function test_index_with_quote_in_key_does_not_break_out_of_includes_js_string(): void
    {
        $html = $this->renderWithKey("a'b");

        self::assertStringNotContainsString("includes('a&#039;b')", $html);
        self::assertStringNotContainsString("includes('a'b')", $html);
    }

    public function test_index_with_quote_in_key_does_not_break_out_of_toggle_js_string(): void
    {
        $html = $this->renderWithKey("a'b");

        self::assertStringNotContainsString("toggle('a&#039;b'", $html);
        self::assertStringNotContainsString("toggle('a'b'", $html);
    }

    public function test_index_with_quote_in_key_does_not_break_out_of_delete_confirm_message(): void
    {
        $html = $this->renderWithKey("a'b");

        self::assertStringNotContainsString("Record #a&#039;b", $html);
        self::assertStringNotContainsString("Record #a'b", $html);
    }

    public function test_index_with_quote_in_key_renders_js_encoded_key_in_alpine_expressions(): void
    {
        $html = $this->renderWithKey("a'b");

        self::assertGreaterThanOrEqual(
            3,
            substr_count($html, 'a' . chr(92) . 'u0027b'),
            'includes(), toggle(), :checked includes() and delete message must use Js::from-style encoding',
        );
    }

    public function test_index_actions_header_uses_translation_in_uk_locale(): void
    {
        $this->app->setLocale('uk');

        $html = $this->renderWithKey('5');

        self::assertMatchesRegularExpression('/<th data-bp-row-controls[^>]*>\s*Дії\s*<\/th>/u', $html);
    }

    public function test_index_actions_header_uses_translation_in_en_locale(): void
    {
        $this->app->setLocale('en');

        $html = $this->renderWithKey('5');

        self::assertMatchesRegularExpression('/<th data-bp-row-controls[^>]*>\s*Actions\s*<\/th>/', $html);
    }
}
