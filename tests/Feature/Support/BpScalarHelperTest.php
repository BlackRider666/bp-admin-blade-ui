<?php

declare(strict_types=1);

namespace BlackParadise\LaravelAdminBladeUI\Tests\Feature\Support;

use BlackParadise\LaravelAdminBladeUI\Tests\TestCase;
use InvalidArgumentException;

enum StubStringEnum: string
{
    case Active = 'active';
    case Inactive = 'inactive';
}

enum StubIntEnum: int
{
    case One = 1;
    case Two = 2;
}

class StubValueObject
{
    public string $value = 'vo-value';
}

class StubValueObjectNullValue
{
    // Explicit null: property_exists() returns true, isset() returns false.
    public ?string $value = null;
}

class StubValueObjectWithMethod
{
    public function value(): string
    {
        return 'method-value';
    }
}

class StubValueObjectWithRequiredArgMethod
{
    // value() requires an argument — must NOT be called by bp_scalar().
    public function value(int $x): int
    {
        return $x * 2;
    }

    public function __toString(): string
    {
        return 'tostring-fallback';
    }
}

class StubToStringObject
{
    public function __toString(): string
    {
        return 'tostring-value';
    }
}

class StubValueObjectPrivateValue
{
    // Encapsulated VO: its $value is private, so reading it from outside the
    // class would throw "Cannot access private property". bp_scalar() must
    // prefer the public value() accessor and never touch the property.
    public function __construct(private int $value = 1) {}

    public function value(): int
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }
}

class StubUncoercibleObject
{
    // No public value() accessor, no public $value, no __toString: there is no
    // way to coerce this to a scalar, so bp_scalar() must throw instead of
    // silently returning the object (which would fail obscurely downstream).
    private string $value = 'hidden';
}

final class BpScalarHelperTest extends TestCase
{
    public function test_backed_string_enum_returns_value(): void
    {
        self::assertSame('active', bp_scalar(StubStringEnum::Active));
    }

    public function test_backed_int_enum_returns_value(): void
    {
        self::assertSame(1, bp_scalar(StubIntEnum::One));
    }

    public function test_object_with_public_value_property_returns_it(): void
    {
        self::assertSame('vo-value', bp_scalar(new StubValueObject()));
    }

    public function test_object_with_value_method_returns_it(): void
    {
        self::assertSame('method-value', bp_scalar(new StubValueObjectWithMethod()));
    }

    public function test_object_with_tostring_returns_string(): void
    {
        self::assertSame('tostring-value', bp_scalar(new StubToStringObject()));
    }

    public function test_scalar_string_passes_through(): void
    {
        self::assertSame('hello', bp_scalar('hello'));
    }

    public function test_scalar_int_passes_through(): void
    {
        self::assertSame(42, bp_scalar(42));
    }

    public function test_null_passes_through(): void
    {
        self::assertNull(bp_scalar(null));
    }

    // ------------------------------------------------------------------
    // bp_scalar edge-case fixes
    // ------------------------------------------------------------------

    /**
     * property_exists() must be used instead of isset() so that an explicitly
     * null public $value property is still coerced (not skipped).
     */
    public function test_object_with_null_value_property_returns_null(): void
    {
        self::assertNull(bp_scalar(new StubValueObjectNullValue()));
    }

    /**
     * An object whose value() method requires arguments must NOT trigger an
     * ArgumentCountError. bp_scalar() must fall back to __toString() instead.
     */
    public function test_object_with_required_arg_value_method_falls_back_to_tostring(): void
    {
        self::assertSame('tostring-fallback', bp_scalar(new StubValueObjectWithRequiredArgMethod()));
    }

    /**
     * A VO with an encapsulated (private) $value must be coerced via its public
     * value() accessor. The accessor is preferred over a direct property read,
     * which would throw "Cannot access private property" for a private $value.
     */
    public function test_object_with_private_value_property_uses_value_method(): void
    {
        self::assertSame(1, bp_scalar(new StubValueObjectPrivateValue()));
    }

    /**
     * An object exposing no coercion path (no public value(), no public $value,
     * no __toString) cannot be reduced to a scalar. bp_scalar() must throw a
     * clear exception rather than return the object as-is and fail obscurely
     * downstream (e.g. when used as an array offset or string-cast).
     */
    public function test_object_without_coercion_path_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);

        bp_scalar(new StubUncoercibleObject());
    }
}
