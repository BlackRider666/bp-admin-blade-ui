<?php

declare(strict_types=1);

namespace BlackParadise\LaravelAdminBladeUI\Tests\Feature\Views;

use BlackParadise\CoreAdmin\Domain\Fields\Base\AbstractField;
use BlackParadise\LaravelAdminBladeUI\Tests\TestCase;
use Illuminate\Support\Facades\View;

/**
 * Slice-3 follow-up (N2/M2): the select display partial must never 500 on a
 * non-scalar $value. `field-display/select.blade.php` indexes
 * `meta()['options'][$value]`; an array $value would raise a TypeError (illegal
 * offset) → HTTP 500 — against the slice's never-500 intent. A `is_scalar()` guard
 * must render the safe placeholder instead.
 */
final class SelectDisplayArrayValueGuardTest extends TestCase
{
    private function makeSelectField(string $name): AbstractField
    {
        return new class ($name) extends AbstractField {
            public function __construct(string $name)
            {
                parent::__construct($name, ucfirst($name), [], true, true, true, false, false, []);
            }

            public static function make(string $name): static
            {
                return new self($name);
            }

            public function type(): string
            {
                return 'select';
            }

            /**
             * @return array<string, mixed>
             */
            public function meta(): array
            {
                return array_merge(parent::meta(), ['options' => ['active' => 'Active']]);
            }
        };
    }

    public function test_select_partial_with_array_value_renders_safely_without_throwing(): void
    {
        $field = $this->makeSelectField('status');

        // A non-scalar value must NOT raise a TypeError on the options[$value] lookup.
        $html = View::make('bpadmin::components.field-display.select', [
            'field' => $field,
            'value' => ['unexpected', 'array'],
        ])->render();

        self::assertIsString($html);
        self::assertStringContainsString('—', $html, 'a non-scalar select value must fall back to the placeholder');
    }
}
