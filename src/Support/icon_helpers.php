<?php

declare(strict_types=1);

use BlackParadise\LaravelAdminBladeUI\Support\IconSet;
use Illuminate\Support\Facades\Lang;

if (!function_exists('bp_icon')) {
    /**
     * Render an inline SVG icon by name.
     *
     * Resolution order (first match wins):
     *   1. Raw markup — if $name itself is a full <svg ...> string, it is
     *      returned verbatim (the caller controls sizing).
     *   2. The bundled {@see IconSet}.
     *   3. config('bpadmin.icons') — app-defined icons, name => inner markup
     *      (or a full <svg> string). This is the extension point for custom
     *      icons without touching the package.
     *   4. Fallback to IconSet::DEFAULT.
     *
     * Resolved *inner* markup (paths) is wrapped into a sized, stroke-styled
     * <svg>. A resolved value that is already a full <svg> is returned as-is.
     *
     * The result is raw HTML — echo it with {!! bp_icon(...) !!} in Blade.
     */
    function bp_icon(string $name, string $class = 'h-4 w-4'): string
    {
        $isSvg = static fn(string $v): bool => str_starts_with(ltrim($v), '<svg');

        // 1. Caller passed a full SVG straight through.
        if ($isSvg($name)) {
            return $name;
        }

        // 2 + 3. Resolve inner markup from the bundled set, then app config.
        $custom = config('bpadmin.icons', []);
        $inner = IconSet::get($name) ?? ($custom[$name] ?? null);

        // 4. Fallback to the default icon.
        if ($inner === null || $inner === '') {
            $inner = IconSet::get(IconSet::DEFAULT) ?? '';
        }

        // A custom value may itself be a complete <svg> — honour it as-is.
        if ($isSvg($inner)) {
            return $inner;
        }

        return '<svg class="' . e($class) . '" fill="none" stroke="currentColor" '
            . 'viewBox="0 0 24 24" aria-hidden="true">' . $inner . '</svg>';
    }
}

if (!function_exists('bp_menu_label')) {
    /**
     * Resolve a sidebar menu label (group label or per-item override) from config.
     *
     * The configured value may be a plain literal ('Catalog') or a translation
     * key ('bpadmin::ui.catalog', 'menu.catalog', ...). When it resolves as an
     * existing translation key it is localized; otherwise it is returned
     * verbatim — so authors can use whichever they prefer.
     *
     * Lives here (blade-ui) rather than in bp-laravel-admin's translation
     * helpers because the sidebar is the only consumer and it must work without
     * waiting on a laravel-admin release.
     */
    function bp_menu_label(string $value): string
    {
        if (Lang::has($value)) {
            $translated = Lang::get($value);

            if (is_string($translated)) {
                return $translated;
            }
        }

        return $value;
    }
}

if (!function_exists('bp_relation_label')) {
    /**
     * Resolve a human-readable label from a relation item for display purposes.
     *
     * Handles the case where the display field is a translatable value stored
     * as a JSON string or a PHP array (e.g. ['en' => 'City', 'uk' => 'Місто']).
     * Falls back to the locale default, then the first locale, then the item id.
     *
     * Resolution order:
     *   1. Scalar $item (not array/object) → (string) $item
     *   2. $item[$displayKey] or $item['name'] or $item['title']:
     *      a. JSON string starting with '{' → decode, then locale-pick
     *      b. PHP array                     → locale-pick
     *      c. Scalar string                 → as-is (strip_tags)
     *   3. No matching display key          → '#' . $item['id'] ?? '?'
     *
     * @param mixed $item The relation item (array or scalar).
     * @param string $displayKey The field name to look up inside the item.
     * @param string $locale BCP-47 locale string (e.g. 'en', 'uk').
     */
    function bp_relation_label(mixed $item, string $displayKey, string $locale): string
    {
        if (!is_array($item) && !is_object($item)) {
            return (string) $item;
        }

        $arr = is_object($item) ? (array) $item : $item;
        $raw = $arr[$displayKey] ?? $arr['name'] ?? $arr['title'] ?? null;

        if (is_string($raw) && str_starts_with(ltrim($raw), '{')) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $raw = $decoded;
            }
        }

        if (is_array($raw)) {
            if (isset($raw[$locale])) {
                $raw = $raw[$locale];
            } else {
                $first = reset($raw);
                $raw   = $first !== false ? $first : null;
            }
        }

        if ($raw === null || $raw === '') {
            return '#' . ($arr['id'] ?? '?');
        }

        return strip_tags((string) $raw);
    }
}

if (!function_exists('bp_scalar')) {
    /**
     * Coerce a value-object, backed enum, or VO-like object to its scalar form.
     *
     * Resolution order:
     *   1. BackedEnum                                  → $v->value
     *   2. Object with public `$value` property        → $v->value
     *      (uses property_exists so null values are also coerced)
     *   3. Object with public no-arg `value()` method  → $v->value()
     *      (skips methods that require arguments to avoid ArgumentCountError)
     *   4. Object with `__toString`                    → (string) $v
     *   5. Anything else (already scalar/null)         → as-is
     *
     * Used in Blade templates before array-offset access or string comparison,
     * so that EnumField display and form rendering survive backed-enum or VO
     * values stored in EntityRecord.
     */
    function bp_scalar(mixed $v): mixed
    {
        if ($v instanceof BackedEnum) {
            return $v->value;
        }

        if (is_object($v)) {
            // property_exists catches null-valued properties too (isset() would miss them).
            if (property_exists($v, 'value') && !($v->value instanceof Closure)) {
                return $v->value;
            }
            if (method_exists($v, 'value')) {
                // Only call value() when it requires no arguments to avoid
                // ArgumentCountError for VOs like `value(int $x)`.
                $ref = new ReflectionMethod($v, 'value');
                if ($ref->getNumberOfRequiredParameters() === 0) {
                    return $v->value();
                }
            }
            if (method_exists($v, '__toString')) {
                return (string) $v;
            }
        }

        return $v;
    }
}
