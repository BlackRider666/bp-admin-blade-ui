<?php

declare(strict_types=1);

namespace BlackParadise\LaravelAdminBladeUI\Tests\Feature\Views;

use BlackParadise\CoreAdmin\Domain\Contracts\LocaleProviderContract;
use BlackParadise\CoreAdmin\Domain\Fields\TranslatableField;
use BlackParadise\LaravelAdminBladeUI\Tests\TestCase;
use Illuminate\Support\Js;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;

/**
 * Big-patch @js sweep: PHP strings interpolated into Alpine/JS expressions must be JS-encoded
 * (Js::from / @js), not merely HTML-escaped. Asserts on rendered Blade output (not the bundle).
 */
final class JsInterpolationEscapingTest extends TestCase
{
    private const APOS = "пам'ять";
    private const TAG = "x</script><b>'\"";

    private function js(string $value): string
    {
        return (string) Js::from($value);
    }

    private function renderThemeToggle(string $light, string $dark): string
    {
        $this->app['translator']->addLines([
            'ui.switch_to_light' => $light,
            'ui.switch_to_dark' => $dark,
        ], $this->app->getLocale(), 'bpadmin');

        return view('bpadmin::components.theme-toggle')->render();
    }

    private function renderTranslatable(string $locale, string $innerType): string
    {
        $this->app->instance(LocaleProviderContract::class, new class ($locale) implements LocaleProviderContract {
            public function __construct(private readonly string $locale) {}

            public function availableLocales(): array
            {
                return [$this->locale];
            }

            public function defaultLocale(): string
            {
                return $this->locale;
            }

            public function currentLocale(): string
            {
                return $this->locale;
            }
        });

        // Real FieldContract implementation: the view must not duck-type (method_exists) its field.
        $field = TranslatableField::make('title');
        if ($innerType === 'editor') {
            $field->asEditor();
        }

        return view('bpadmin::components.field.translatable', ['field' => $field, 'value' => []])->render();
    }

    public function test_translatable_input_view_does_not_duck_type_its_field(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3) . '/resources/views/components/field/translatable.blade.php');

        self::assertIsString($source);
        self::assertStringNotContainsString('method_exists', $source);
    }

    public function test_theme_toggle_with_apostrophe_translation_does_not_break_js_string(): void
    {
        $html = $this->renderThemeToggle(self::APOS, 'dark');

        self::assertStringNotContainsString("'пам&#039;ять'", $html);
        self::assertStringNotContainsString("'пам'ять'", $html);
    }

    public function test_theme_toggle_renders_js_encoded_translations(): void
    {
        $html = $this->renderThemeToggle(self::APOS, self::TAG);

        self::assertStringContainsString($this->js(self::APOS), $html);
        self::assertStringContainsString($this->js(self::TAG), $html);
    }

    public function test_toasts_with_apostrophe_message_renders_js_encoded_message(): void
    {
        $this->app['session']->driver()->flash('success', self::APOS);

        $html = view('bpadmin::components.toasts', ['errors' => new ViewErrorBag()])->render();

        self::assertStringContainsString($this->js(self::APOS), $html);
        self::assertStringNotContainsString("'пам&#039;ять'", $html);
    }

    public function test_toasts_validation_error_with_script_tag_renders_js_encoded(): void
    {
        $bag = new ViewErrorBag();
        $bag->put('default', new MessageBag(['f' => [self::TAG]]));

        $html = view('bpadmin::components.toasts', ['errors' => $bag])->render();

        self::assertStringContainsString($this->js(self::TAG), $html);
        self::assertStringNotContainsString('x</script>', $html);
    }

    public function test_translatable_with_quote_in_locale_does_not_break_active_tab_string(): void
    {
        $html = $this->renderTranslatable("o'k", 'text');

        self::assertStringNotContainsString("activeTab: 'o&#039;k'", $html);
        self::assertStringNotContainsString("activeTab = 'o&#039;k'", $html);
        self::assertStringNotContainsString("activeTab === 'o&#039;k'", $html);
    }

    public function test_translatable_renders_js_encoded_locale_in_alpine_expressions(): void
    {
        $html = $this->renderTranslatable("o'k", 'text');

        self::assertGreaterThanOrEqual(
            4,
            substr_count($html, $this->js("o'k")),
            'x-data default tab, @click, :class and x-show must use Js::from-style encoding',
        );
    }

    public function test_translatable_editor_with_quote_in_locale_encodes_editor_init_id(): void
    {
        $html = $this->renderTranslatable("o'k", 'editor');

        self::assertStringNotContainsString("init('translatable-title-editor-o&#039;k')", $html);
        self::assertStringNotContainsString("init('translatable-title-editor-o'k')", $html);
    }
}
