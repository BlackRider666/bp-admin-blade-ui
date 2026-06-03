<?php

declare(strict_types=1);

namespace BlackParadise\LaravelAdminBladeUI\Tests\Feature\Views;

use BlackParadise\CoreAdmin\Domain\Fields\Base\AbstractField;
use BlackParadise\LaravelAdminBladeUI\Tests\TestCase;
use Illuminate\Support\Facades\View;

enum MultiStatusEnum: string
{
    case Active   = 'active';
    case Inactive = 'inactive';
    case Pending  = 'pending';
}

/**
 * Regression test for Bug #3-multi: the multi-enum input fatals when the
 * value array contains backed-enum objects, because strval() cannot convert
 * a backed enum to string (no __toString on backed enums).
 *
 * The fix: use bp_scalar() inside array_map in enum.blade.php so that
 * both plain strings and backed-enum objects are handled correctly.
 *
 * This covers the INPUT side (enum.blade.php with $isMultiple = true).
 * The display side was already covered by EnumFieldDisplayCoercionTest.
 */
final class EnumFieldInputMultiBackedEnumTest extends TestCase
{
    /**
     * Build a minimal anonymous enum field that reports as multiple.
     *
     * @param array<string, string> $options
     */
    private function makeMultiEnumField(string $name, array $options): AbstractField
    {
        return new class($name, $options) extends AbstractField {
            /** @param array<string, string> $options */
            public function __construct(
                string $name,
                private readonly array $options,
            ) {
                parent::__construct($name, ucfirst($name), [], true, true, true, false, false, []);
            }

            public static function make(string $name): static
            {
                return new static($name, []);
            }

            public function type(): string
            {
                return 'enum';
            }

            public function meta(): array
            {
                return array_merge(parent::meta(), [
                    'options'  => $this->options,
                    'multiple' => true,
                ]);
            }
        };
    }

    /**
     * Core assertion: rendering a multi-enum input with backed-enum values
     * must not throw AND must mark the corresponding options as selected.
     */
    public function test_multi_enum_input_backed_enum_values_render_selected_options(): void
    {
        $field = $this->makeMultiEnumField('status', [
            'active'   => 'Active',
            'inactive' => 'Inactive',
            'pending'  => 'Pending',
        ]);

        // This is the exact scenario that triggered the Fatal:
        // strval() on a backed enum → Error: Object of class ... could not be converted to string.
        $value = [MultiStatusEnum::Active, MultiStatusEnum::Inactive];

        $html = View::make('bpadmin::components.field.enum', [
            'field'    => $field,
            'value'    => $value,
            'name'     => 'status',
            'errorKey' => 'status',
        ])->render();

        // Both selected enums must produce a <option ... selected> element.
        self::assertMatchesRegularExpression(
            '/<option\s[^>]*value="active"[^>]*selected[^>]*>/',
            $html,
            'Option "active" must be marked selected.',
        );
        self::assertMatchesRegularExpression(
            '/<option\s[^>]*value="inactive"[^>]*selected[^>]*>/',
            $html,
            'Option "inactive" must be marked selected.',
        );

        // The unselected option must NOT carry the selected attribute.
        // A simple check: "pending" option exists but without selected.
        self::assertStringContainsString('value="pending"', $html);
        self::assertDoesNotMatch(
            '/<option\s[^>]*value="pending"[^>]*selected/',
            $html,
        );
    }

    public function test_multi_enum_input_plain_string_values_still_work(): void
    {
        $field = $this->makeMultiEnumField('status', [
            'active'   => 'Active',
            'inactive' => 'Inactive',
        ]);

        $value = ['active'];

        $html = View::make('bpadmin::components.field.enum', [
            'field'    => $field,
            'value'    => $value,
            'name'     => 'status',
            'errorKey' => 'status',
        ])->render();

        self::assertMatchesRegularExpression(
            '/<option\s[^>]*value="active"[^>]*selected[^>]*>/',
            $html,
        );
    }

    public function test_multi_enum_input_empty_value_renders_no_selected(): void
    {
        $field = $this->makeMultiEnumField('status', [
            'active'   => 'Active',
            'inactive' => 'Inactive',
        ]);

        $html = View::make('bpadmin::components.field.enum', [
            'field'    => $field,
            'value'    => [],
            'name'     => 'status',
            'errorKey' => 'status',
        ])->render();

        self::assertStringNotContainsString('selected', $html);
    }

    public function test_multi_enum_input_null_value_renders_no_selected(): void
    {
        $field = $this->makeMultiEnumField('status', [
            'active'   => 'Active',
            'inactive' => 'Inactive',
        ]);

        $html = View::make('bpadmin::components.field.enum', [
            'field'    => $field,
            'value'    => null,
            'name'     => 'status',
            'errorKey' => 'status',
        ])->render();

        self::assertStringNotContainsString('selected', $html);
    }

    /**
     * Mix of plain strings and backed-enum objects in the same value array.
     */
    public function test_multi_enum_input_mixed_string_and_backed_enum_values(): void
    {
        $field = $this->makeMultiEnumField('status', [
            'active'   => 'Active',
            'inactive' => 'Inactive',
            'pending'  => 'Pending',
        ]);

        $value = ['active', MultiStatusEnum::Pending];

        $html = View::make('bpadmin::components.field.enum', [
            'field'    => $field,
            'value'    => $value,
            'name'     => 'status',
            'errorKey' => 'status',
        ])->render();

        self::assertMatchesRegularExpression(
            '/<option\s[^>]*value="active"[^>]*selected[^>]*>/',
            $html,
        );
        self::assertMatchesRegularExpression(
            '/<option\s[^>]*value="pending"[^>]*selected[^>]*>/',
            $html,
        );
    }

    private function assertDoesNotMatch(string $pattern, string $string): void
    {
        self::assertDoesNotMatchRegularExpression($pattern, $string);
    }
}
