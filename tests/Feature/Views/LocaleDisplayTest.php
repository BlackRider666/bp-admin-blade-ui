<?php

declare(strict_types=1);

namespace BlackParadise\LaravelAdminBladeUI\Tests\Feature\Views;

use BlackParadise\CoreAdmin\Domain\Entity\EntityRecord;
use BlackParadise\CoreAdmin\Domain\Fields\TranslatableField;
use BlackParadise\LaravelAdminBladeUI\Tests\Fixtures\StubEntityDefinition;
use BlackParadise\LaravelAdminBladeUI\Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

/**
 * Task D.2 — DISPLAY consumers must use currentLocale() (runtime locale),
 * not defaultLocale() (static config), so that switching locale in the navbar
 * updates translatable values, relation labels, and the default form locale tab.
 *
 * Covers bug #9 (locale-switcher inert in display layer).
 */
final class LocaleDisplayTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $request = Request::create('/admin/test', 'GET');
        $request->setLaravelSession($this->app['session']->driver());
        $this->app->instance('request', $request);
    }

    /**
     * The translatable component must initialise Alpine's activeTab to the
     * RUNTIME locale (app()->getLocale()), not the static config locale.
     *
     * Before fix: uses defaultLocale() → activeTab stays 'en' even when locale
     * is switched to 'de' at runtime.
     * After fix:  uses currentLocale() → activeTab correctly becomes 'de'.
     */
    public function test_translatable_active_tab_follows_runtime_locale(): void
    {
        app()->setLocale('de');
        config(['bpadmin.locales' => ['en', 'de']]);

        $field = TranslatableField::make('title');

        $html = View::make('bpadmin::components.field.translatable', [
            'field' => $field,
            'value' => ['en' => 'Hello', 'de' => 'Hallo'],
        ])->render();

        self::assertStringContainsString("activeTab: 'de'", $html);
    }

    /**
     * When the runtime locale is the default locale (en), the activeTab must
     * still equal 'en' — no regression on the happy path.
     */
    public function test_translatable_active_tab_is_default_locale_when_no_switch(): void
    {
        app()->setLocale('en');
        config(['bpadmin.locales' => ['en', 'de']]);

        $field = TranslatableField::make('title');

        $html = View::make('bpadmin::components.field.translatable', [
            'field' => $field,
            'value' => ['en' => 'Hello', 'de' => 'Hallo'],
        ])->render();

        self::assertStringContainsString("activeTab: 'en'", $html);
    }

    /**
     * field-display (translatable case) must resolve the displayed value from
     * the runtime locale, not the static default locale.
     *
     * Before fix: displays 'Hello' (en) even when locale is 'de'.
     * After fix:  displays 'Hallo' (de).
     */
    public function test_field_display_translatable_resolves_runtime_locale(): void
    {
        app()->setLocale('de');

        $definition = new StubEntityDefinition();
        $field = TranslatableField::make('name');

        $record = new EntityRecord(
            $definition,
            ['id' => 1, 'name' => ['en' => 'Hello', 'de' => 'Hallo'], 'email' => 'test@test.com'],
        );

        $html = View::make('bpadmin::components.field-display', [
            'field'      => $field,
            'record'     => $record,
            'definition' => $definition,
        ])->render();

        self::assertStringContainsString('Hallo', $html);
        self::assertStringNotContainsString('Hello', $html);
    }
}
