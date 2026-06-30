<?php

declare(strict_types=1);

namespace BlackParadise\LaravelAdminBladeUI\Tests\Feature\Views;

use BlackParadise\CoreAdmin\Domain\Entity\EntityRecord;
use BlackParadise\CoreAdmin\Domain\Fields\Base\AbstractField;
use BlackParadise\CoreAdmin\Domain\Fields\DateField;
use BlackParadise\CoreAdmin\Domain\Fields\DateTimeField;
use BlackParadise\CoreAdmin\Domain\Fields\NumberField;
use BlackParadise\CoreAdmin\Domain\Fields\PhoneField;
use BlackParadise\CoreAdmin\Domain\Fields\TextField;
use BlackParadise\LaravelAdminBladeUI\Tests\Fixtures\StubEntityDefinition;
use BlackParadise\LaravelAdminBladeUI\Tests\TestCase;
use Illuminate\Support\Facades\View;

/**
 * A9 / A11 — field-display C4 render dispatch.
 *
 * A9: display of number / select / phone / text must use per-type partials
 *     (field-display/{type}.blade.php) via @includeFirst, NOT the monolithic
 *     @default arm that produces Str::limit((string) $value, 80).
 *
 * A11: garbage value in a date / datetime display must never throw; the
 *      display arm is ALREADY guarded pre-IMPL, so these tests are pre-green
 *      (regression guards that the DateFormatter refactor must preserve).
 */
final class FieldDisplayC4RenderTest extends TestCase
{
    private StubEntityDefinition $definition;

    protected function setUp(): void
    {
        parent::setUp();
        $this->definition = new StubEntityDefinition();
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Build a minimal select-type field stub whose meta() exposes an options map.
     *
     * @param array<string, string> $options
     */
    private function makeSelectField(string $name, array $options): AbstractField
    {
        return new class ($name, $options) extends AbstractField {
            /**
             * @param array<string, string> $options
             */
            public function __construct(string $name, private readonly array $options)
            {
                parent::__construct($name, ucfirst($name), [], true, true, true, false, false, []);
            }

            public static function make(string $name): static
            {
                return new self($name, []);
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
                return array_merge(parent::meta(), ['options' => $this->options]);
            }
        };
    }

    private function renderFieldDisplay(AbstractField $field, mixed $value): string
    {
        $record = new EntityRecord(
            $this->definition,
            ['id' => 1, 'name' => (string) ($value ?? ''), 'email' => 'stub@test.com'],
        );

        // Override the field value by injecting via a custom field name mapped on the record.
        // We re-create the record with the field name → value mapping so $record->get() returns it.
        $attributes = ['id' => 1, 'name' => 'stub', 'email' => 'stub@test.com', $field->name() => $value];
        $record = new EntityRecord($this->definition, $attributes);

        return View::make('bpadmin::components.field-display', [
            'field'      => $field,
            'record'     => $record,
            'definition' => $this->definition,
        ])->render();
    }

    // -------------------------------------------------------------------------
    // A9 – behaviour: select field shows option LABEL, not raw stored key
    // -------------------------------------------------------------------------

    /**
     * RED: currently 'select' type falls to @default which emits the raw key
     * via Str::limit((string) $value, 80). After IMPL the partial resolves
     * the human-readable label from meta()['options'].
     */
    public function test_select_field_display_shows_option_label_not_raw_key(): void
    {
        $field = $this->makeSelectField('status', [
            'active'   => 'Active Status',
            'inactive' => 'Inactive Status',
        ]);

        $html = $this->renderFieldDisplay($field, 'active');

        // Must show the human label; raw key is internal and must NOT be the only output.
        self::assertStringContainsString('Active Status', $html);
    }

    // -------------------------------------------------------------------------
    // A9 – behaviour: phone field renders a tel: link
    // -------------------------------------------------------------------------

    /**
     * RED: currently 'phone' type falls to @default which outputs the number
     * as plain text. After IMPL the partial wraps it in <a href="tel:...">.
     */
    public function test_phone_field_display_renders_tel_link(): void
    {
        $field = PhoneField::make('phone');
        $html  = $this->renderFieldDisplay($field, '+380501234567');

        self::assertStringContainsString('tel:', $html);
    }

    // -------------------------------------------------------------------------
    // A9 – source / structure guard: @includeFirst dispatch
    // -------------------------------------------------------------------------

    /**
     * RED: currently field-display.blade.php uses a monolithic @switch.
     * After IMPL it must use @includeFirst so per-type partials are resolved.
     */
    public function test_field_display_source_uses_include_first_dispatch(): void
    {
        $sourcePath = __DIR__ . '/../../../resources/views/components/field-display.blade.php';

        self::assertFileExists($sourcePath);
        self::assertStringContainsString(
            '@includeFirst',
            (string) file_get_contents($sourcePath),
        );
    }

    // -------------------------------------------------------------------------
    // A9 – structure guard: the 4 per-type partial files must exist
    // -------------------------------------------------------------------------

    public function test_field_display_select_partial_file_exists(): void
    {
        $path = __DIR__ . '/../../../resources/views/components/field-display/select.blade.php';

        self::assertFileExists($path);
    }

    public function test_field_display_phone_partial_file_exists(): void
    {
        $path = __DIR__ . '/../../../resources/views/components/field-display/phone.blade.php';

        self::assertFileExists($path);
    }

    public function test_field_display_number_partial_file_exists(): void
    {
        $path = __DIR__ . '/../../../resources/views/components/field-display/number.blade.php';

        self::assertFileExists($path);
    }

    // text.blade.php intentionally has NO dedicated partial — it is byte-identical
    // to _default, so 'text' falls through @includeFirst to _default (M3/S3 cleanup).
    // Its removal is asserted by DisplayPartialBacklogSourceGuardTest.

    // -------------------------------------------------------------------------
    // A11 – view-level regression guards (PRE-GREEN — already guarded by
    //        try/catch in the date/datetime arms; must stay green after IMPL)
    // -------------------------------------------------------------------------

    /**
     * PRE-GREEN: date arm already wraps Carbon::parse in try/catch.
     * Declared as --pre-green in bp-red because the guard is pre-IMPL.
     * This test is a regression guard: the DateFormatter refactor must preserve
     * the no-throw + placeholder behaviour.
     */
    public function test_date_field_display_garbage_value_shows_placeholder_not_exception(): void
    {
        $field = DateField::make('published_at');
        $html  = $this->renderFieldDisplay($field, 'not-a-date');

        // Must render the dash placeholder, no exception thrown.
        self::assertStringContainsString('—', $html);
    }

    /**
     * PRE-GREEN: datetime arm already wraps Carbon::parse in try/catch.
     * Same reasoning as above.
     */
    public function test_datetime_field_display_garbage_value_shows_placeholder_not_exception(): void
    {
        $field = DateTimeField::make('created_at');
        $html  = $this->renderFieldDisplay($field, 'not-a-date');

        self::assertStringContainsString('—', $html);
    }

    // -------------------------------------------------------------------------
    // A9 – additional structural smoke: number and text do not throw
    //      (they currently fall to @default; these confirm dispatch without crash)
    // -------------------------------------------------------------------------

    public function test_number_field_display_renders_without_throwing(): void
    {
        $field = NumberField::make('qty');
        $html  = $this->renderFieldDisplay($field, 42);

        self::assertStringContainsString('42', $html);
    }

    public function test_text_field_display_renders_without_throwing(): void
    {
        $field = TextField::make('name');
        $html  = $this->renderFieldDisplay($field, 'Hello World');

        self::assertStringContainsString('Hello World', $html);
    }
}
