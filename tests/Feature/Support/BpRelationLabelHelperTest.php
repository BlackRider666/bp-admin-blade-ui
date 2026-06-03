<?php

declare(strict_types=1);

namespace BlackParadise\LaravelAdminBladeUI\Tests\Feature\Support;

use BlackParadise\LaravelAdminBladeUI\Tests\TestCase;

/**
 * Unit tests for bp_relation_label() helper.
 *
 * Verifies that:
 *   - scalar items are cast to string as-is
 *   - plain-string display values are returned via strip_tags
 *   - array display values (translatable) are resolved to the correct locale
 *   - JSON-encoded translatable strings are decoded and locale-picked
 *   - fallback to first locale when the exact locale is missing
 *   - fallback to '#id' when no display key matches
 */
final class BpRelationLabelHelperTest extends TestCase
{
    // ------------------------------------------------------------------
    // Scalar item pass-through
    // ------------------------------------------------------------------

    public function test_scalar_string_item_returns_as_string(): void
    {
        self::assertSame('London', bp_relation_label('London', 'name', 'en'));
    }

    public function test_scalar_int_item_returns_as_string(): void
    {
        self::assertSame('42', bp_relation_label(42, 'name', 'en'));
    }

    // ------------------------------------------------------------------
    // Plain (non-translatable) display field
    // ------------------------------------------------------------------

    public function test_array_item_plain_string_display_field(): void
    {
        $item = ['id' => 1, 'name' => 'Paris'];
        self::assertSame('Paris', bp_relation_label($item, 'name', 'en'));
    }

    public function test_array_item_uses_custom_display_key(): void
    {
        $item = ['id' => 1, 'title' => 'Laravel News'];
        self::assertSame('Laravel News', bp_relation_label($item, 'title', 'en'));
    }

    public function test_array_item_strips_html_tags(): void
    {
        $item = ['id' => 1, 'name' => '<b>Bold City</b>'];
        self::assertSame('Bold City', bp_relation_label($item, 'name', 'en'));
    }

    // ------------------------------------------------------------------
    // Translatable display field — PHP array value
    // ------------------------------------------------------------------

    public function test_translatable_array_picks_requested_locale(): void
    {
        $item = ['id' => 3, 'name' => ['en' => 'English City', 'uk' => 'Місто']];
        self::assertSame('English City', bp_relation_label($item, 'name', 'en'));
    }

    public function test_translatable_array_picks_uk_locale(): void
    {
        $item = ['id' => 3, 'name' => ['en' => 'English City', 'uk' => 'Місто']];
        self::assertSame('Місто', bp_relation_label($item, 'name', 'uk'));
    }

    public function test_translatable_array_falls_back_to_first_when_locale_missing(): void
    {
        $item = ['id' => 3, 'name' => ['de' => 'Berlin', 'fr' => 'Paris']];
        // locale='en' not present → fallback to first value
        self::assertSame('Berlin', bp_relation_label($item, 'name', 'en'));
    }

    // ------------------------------------------------------------------
    // Translatable display field — JSON-encoded string
    // ------------------------------------------------------------------

    public function test_translatable_json_string_picks_locale(): void
    {
        $json = json_encode(['en' => 'London', 'uk' => 'Лондон']);
        $item = ['id' => 4, 'name' => $json];
        self::assertSame('London', bp_relation_label($item, 'name', 'en'));
    }

    public function test_translatable_json_string_picks_uk(): void
    {
        $json = json_encode(['en' => 'London', 'uk' => 'Лондон']);
        $item = ['id' => 4, 'name' => $json];
        self::assertSame('Лондон', bp_relation_label($item, 'name', 'uk'));
    }

    // ------------------------------------------------------------------
    // Fallback paths
    // ------------------------------------------------------------------

    public function test_falls_back_to_name_key_when_display_key_missing(): void
    {
        $item = ['id' => 5, 'name' => 'Fallback Name'];
        // display key is 'title', but item has no 'title' — falls back to 'name'
        self::assertSame('Fallback Name', bp_relation_label($item, 'title', 'en'));
    }

    public function test_falls_back_to_title_key_when_display_and_name_missing(): void
    {
        $item = ['id' => 6, 'title' => 'Title Fallback'];
        self::assertSame('Title Fallback', bp_relation_label($item, 'slug', 'en'));
    }

    public function test_falls_back_to_hash_id_when_no_display_keys_match(): void
    {
        $item = ['id' => 7, 'slug' => 'some-slug'];
        // Neither 'label', 'name', nor 'title' present
        self::assertSame('#7', bp_relation_label($item, 'label', 'en'));
    }

    public function test_falls_back_to_hash_question_mark_when_no_id_either(): void
    {
        $item = ['slug' => 'x'];
        self::assertSame('#?', bp_relation_label($item, 'label', 'en'));
    }
}
