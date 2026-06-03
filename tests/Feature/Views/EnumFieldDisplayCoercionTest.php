<?php

declare(strict_types=1);

namespace BlackParadise\LaravelAdminBladeUI\Tests\Feature\Views;

use BackedEnum;
use BlackParadise\CoreAdmin\Domain\Entity\EntityRecord;
use BlackParadise\CoreAdmin\Domain\Fields\Base\AbstractField;
use BlackParadise\CoreAdmin\Domain\Fields\EnumField;
use BlackParadise\LaravelAdminBladeUI\Tests\Fixtures\StubEntityDefinition;
use BlackParadise\LaravelAdminBladeUI\Tests\TestCase;
use Illuminate\Support\Facades\View;

enum StubStatusEnum: string
{
    case Active   = 'active';
    case Inactive = 'inactive';
}

/**
 * Regression test for Bug #3: field-display must not throw when $value is a
 * backed enum or VO — it must coerce to scalar before array-offset access.
 *
 * Requires bp_scalar() to be applied in field-display.blade.php before
 * $enumOptions[$value] and $enumOptions[$item] lookups.
 */
final class EnumFieldDisplayCoercionTest extends TestCase
{
    private StubEntityDefinition $definition;

    protected function setUp(): void
    {
        parent::setUp();
        $this->definition = new StubEntityDefinition();
    }

    /**
     * Build a minimal enum field stub whose meta() returns the options array.
     * This avoids vendor version differences in EnumField (older vendor may lack multiple()).
     *
     * @param array<string, string> $options
     */
    private function makeEnumField(string $name, array $options, bool $multiple = false): AbstractField
    {
        return new class($name, $options, $multiple) extends AbstractField {
            public function __construct(
                string $name,
                private readonly array $options,
                private readonly bool  $isMultiple,
            ) {
                parent::__construct($name, ucfirst($name), [], true, true, true, false, false, []);
            }

            public static function make(string $name): static
            {
                // Satisfy abstract; not called in tests.
                return new static($name, [], false);
            }

            public function type(): string
            {
                return 'enum';
            }

            public function meta(): array
            {
                return array_merge(parent::meta(), [
                    'options'  => $this->options,
                    'multiple' => $this->isMultiple,
                ]);
            }
        };
    }

    public function test_field_display_enum_single_backed_enum_value_renders_label(): void
    {
        $field = $this->makeEnumField('status', ['active' => 'Active', 'inactive' => 'Inactive']);

        // EntityRecord stores a backed-enum instance as the attribute value —
        // this is what the bug triggers (Cannot access offset of type X on array).
        $record = new EntityRecord(
            $this->definition,
            ['id' => 1, 'name' => 'X', 'email' => 'x@x.com', 'status' => StubStatusEnum::Active],
        );

        $html = View::make('bpadmin::components.field-display', [
            'field'      => $field,
            'record'     => $record,
            'definition' => $this->definition,
        ])->render();

        self::assertStringContainsString('Active', $html);
    }

    public function test_field_display_enum_multi_backed_enum_values_renders_labels(): void
    {
        $field = $this->makeEnumField('tags', ['active' => 'Active', 'inactive' => 'Inactive'], true);

        $record = new EntityRecord(
            $this->definition,
            ['id' => 1, 'name' => 'X', 'email' => 'x@x.com', 'tags' => [StubStatusEnum::Active, StubStatusEnum::Inactive]],
        );

        $html = View::make('bpadmin::components.field-display', [
            'field'      => $field,
            'record'     => $record,
            'definition' => $this->definition,
        ])->render();

        self::assertStringContainsString('Active', $html);
        self::assertStringContainsString('Inactive', $html);
    }

    public function test_field_display_enum_single_plain_string_value_still_works(): void
    {
        $field = $this->makeEnumField('status', ['active' => 'Active', 'inactive' => 'Inactive']);

        $record = new EntityRecord(
            $this->definition,
            ['id' => 1, 'name' => 'X', 'email' => 'x@x.com', 'status' => 'inactive'],
        );

        $html = View::make('bpadmin::components.field-display', [
            'field'      => $field,
            'record'     => $record,
            'definition' => $this->definition,
        ])->render();

        self::assertStringContainsString('Inactive', $html);
    }

    public function test_field_display_enum_multi_plain_strings_still_work(): void
    {
        $field = $this->makeEnumField('tags', ['active' => 'Active', 'inactive' => 'Inactive'], true);

        $record = new EntityRecord(
            $this->definition,
            ['id' => 1, 'name' => 'X', 'email' => 'x@x.com', 'tags' => ['active', 'inactive']],
        );

        $html = View::make('bpadmin::components.field-display', [
            'field'      => $field,
            'record'     => $record,
            'definition' => $this->definition,
        ])->render();

        self::assertStringContainsString('Active', $html);
        self::assertStringContainsString('Inactive', $html);
    }
}
