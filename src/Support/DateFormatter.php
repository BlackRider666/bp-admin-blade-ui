<?php

declare(strict_types=1);

namespace BlackParadise\LaravelAdminBladeUI\Support;

use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Date;
use Throwable;

final class DateFormatter
{
    /**
     * Parse a date value into a CarbonInterface instance.
     *
     * Returns null for null, empty string, or any unparseable input.
     * Never throws.
     */
    public static function parse(mixed $value): ?CarbonInterface
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Date::parse($value);
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Format a date value using the given format string.
     *
     * Returns null when the value cannot be parsed.
     * Never throws.
     */
    public static function format(mixed $value, string $format): ?string
    {
        return self::parse($value)?->format($format);
    }
}
