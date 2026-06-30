<?php

declare(strict_types=1);

namespace BlackParadise\LaravelAdminBladeUI\Tests\Unit\Support;

use BlackParadise\LaravelAdminBladeUI\Support\DateFormatter;
use Carbon\CarbonInterface;
use PHPUnit\Framework\TestCase;

/**
 * A10 / A11 — DateFormatter must centralise and guard Carbon::parse logic.
 *
 * RED pre-IMPL: DateFormatter does not yet exist at
 * src/Support/DateFormatter.php → every test method produces a PHP Error
 * (class not found) which the JUnit reporter captures as <error>.
 * Both <failure> and <error> are honest RED for bp-red.sh.
 */
final class DateFormatterTest extends TestCase
{
    // -------------------------------------------------------------------------
    // A10 – correct parsing and formatting of valid dates
    // -------------------------------------------------------------------------

    /** @test */
    public function test_parse_with_valid_date_returns_carbon_interface(): void
    {
        $result = DateFormatter::parse('2026-06-28');

        self::assertInstanceOf(CarbonInterface::class, $result);
    }

    /** @test */
    public function test_format_with_valid_date_returns_formatted_string(): void
    {
        $result = DateFormatter::format('2026-06-28', 'd.m.Y');

        self::assertSame('28.06.2026', $result);
    }

    // -------------------------------------------------------------------------
    // A11 – garbage / null / empty inputs must never throw
    // -------------------------------------------------------------------------

    /** @test */
    public function test_parse_with_garbage_input_returns_null_without_throwing(): void
    {
        $result = DateFormatter::parse('not-a-date');

        self::assertNull($result);
    }

    /** @test */
    public function test_format_with_garbage_input_returns_null_without_throwing(): void
    {
        $result = DateFormatter::format('not-a-date', 'd.m.Y');

        self::assertNull($result);
    }

    /** @test */
    public function test_parse_with_null_returns_null(): void
    {
        $result = DateFormatter::parse(null);

        self::assertNull($result);
    }

    /** @test */
    public function test_format_with_null_returns_null(): void
    {
        $result = DateFormatter::format(null, 'd.m.Y');

        self::assertNull($result);
    }

    /** @test */
    public function test_parse_with_empty_string_returns_null(): void
    {
        $result = DateFormatter::parse('');

        self::assertNull($result);
    }

    /** @test */
    public function test_format_with_empty_string_returns_null(): void
    {
        $result = DateFormatter::format('', 'd.m.Y');

        self::assertNull($result);
    }

    // -------------------------------------------------------------------------
    // M1/S3 – non-string inputs must never throw (array, bool, int coverage)
    // -------------------------------------------------------------------------

    /**
     * parse() catches TypeError from Carbon when given an array and returns null.
     * Locks the never-throw contract for the array branch of the catch(Throwable) guard.
     */
    /** @test */
    public function test_parse_with_array_returns_null_without_throwing(): void
    {
        $result = DateFormatter::parse(['x']);

        self::assertNull($result);
    }

    /**
     * Carbon coerces true to int 1 (a valid Unix timestamp) and returns a CarbonInterface.
     * Locks the contract: the method never throws for bool inputs.
     */
    /** @test */
    public function test_parse_with_true_returns_carbon_interface_without_throwing(): void
    {
        $result = DateFormatter::parse(true);

        self::assertInstanceOf(CarbonInterface::class, $result);
    }

    /**
     * Carbon coerces false to int 0 (Unix epoch) and returns a CarbonInterface.
     * Note: false !== '' and false !== null so the early-return guard does NOT fire.
     */
    /** @test */
    public function test_parse_with_false_returns_carbon_interface_without_throwing(): void
    {
        $result = DateFormatter::parse(false);

        self::assertInstanceOf(CarbonInterface::class, $result);
    }

    /**
     * Carbon treats int as a Unix timestamp and returns a CarbonInterface.
     * Locks the contract for numeric-timestamp inputs.
     */
    /** @test */
    public function test_parse_with_int_timestamp_returns_carbon_interface_without_throwing(): void
    {
        $result = DateFormatter::parse(1_234_567_890);

        self::assertInstanceOf(CarbonInterface::class, $result);
    }

    /**
     * format() returns null when parse() returns null (array input → TypeError caught).
     */
    /** @test */
    public function test_format_with_array_returns_null_without_throwing(): void
    {
        $result = DateFormatter::format(['x'], 'd.m.Y');

        self::assertNull($result);
    }

    /**
     * format() returns a formatted string when parse() succeeds (bool true → epoch+1s).
     */
    /** @test */
    public function test_format_with_true_returns_string_without_throwing(): void
    {
        $result = DateFormatter::format(true, 'd.m.Y');

        self::assertIsString($result);
    }
}
