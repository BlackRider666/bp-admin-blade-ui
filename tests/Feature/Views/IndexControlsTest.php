<?php

declare(strict_types=1);

namespace BlackParadise\LaravelAdminBladeUI\Tests\Feature\Views;

use BlackParadise\CoreAdmin\Domain\ValueObjects\Capability;
use BlackParadise\CoreAdmin\Domain\ValueObjects\EntityCapabilities;
use BlackParadise\LaravelAdminBladeUI\Tests\Feature\Views\Concerns\RendersUiPages;
use BlackParadise\LaravelAdminBladeUI\Tests\Fixtures\FakeAuthorization;
use BlackParadise\LaravelAdminBladeUI\Tests\Fixtures\UiStateDefinition;
use BlackParadise\LaravelAdminBladeUI\Tests\TestCase;

/**
 * A29 / A41 (+ A30-ish): create/edit/delete/bulk/empty-CTA controls render only when
 * capability AND authorization allow; read-only index/show render none of them.
 */
final class IndexControlsTest extends TestCase
{
    use RendersUiPages;

    private const CREATE = '/ui_thing/create';
    private const BULK = '/ui_thing/bulk-destroy';

    public function test_index_with_everything_allowed_renders_all_controls(): void
    {
        $html = $this->renderIndex(new UiStateDefinition(), new FakeAuthorization(['*']));

        self::assertStringContainsString(self::CREATE, $html);
        self::assertStringContainsString('/ui_thing/5/edit', $html);
        self::assertStringContainsString('method="POST"', $html);
        self::assertStringContainsString('/ui_thing/5"', $html);
        self::assertStringContainsString(self::BULK, $html);
    }

    public function test_index_when_read_only_hides_create_control(): void
    {
        $html = $this->renderIndex(new UiStateDefinition(EntityCapabilities::readOnly()), new FakeAuthorization(['*']));

        self::assertStringNotContainsString(self::CREATE, $html);
        self::assertStringNotContainsString('data-bp-new', $html);
    }

    public function test_index_when_read_only_hides_edit_delete_and_bulk_controls(): void
    {
        $html = $this->renderIndex(new UiStateDefinition(EntityCapabilities::readOnly()), new FakeAuthorization(['*']));

        self::assertStringNotContainsString('/ui_thing/5/edit', $html);
        self::assertStringNotContainsString('name="_method" value="DELETE"', $html);
        self::assertStringNotContainsString(self::BULK, $html);
        self::assertStringNotContainsString('bpBulkSelect', $html);
    }

    public function test_index_when_read_only_still_renders_the_rows(): void
    {
        $html = $this->renderIndex(new UiStateDefinition(EntityCapabilities::readOnly()), new FakeAuthorization(['*']));

        self::assertStringContainsString('Hello 1', $html);
        self::assertStringContainsString('/ui_thing/5', $html);
    }

    public function test_index_when_read_only_and_empty_hides_empty_state_create_cta(): void
    {
        $html = $this->renderIndex(new UiStateDefinition(EntityCapabilities::readOnly()), new FakeAuthorization(['*']), rows: 0);

        self::assertStringNotContainsString(self::CREATE, $html);
    }

    public function test_index_when_authz_denies_create_hides_create_even_if_capability_allows(): void
    {
        $html = $this->renderIndex(new UiStateDefinition(), FakeAuthorization::denying('create'));

        self::assertStringNotContainsString(self::CREATE, $html);
        self::assertStringContainsString('/ui_thing/5/edit', $html);
    }

    public function test_index_when_authz_denies_create_and_empty_hides_empty_state_cta(): void
    {
        $html = $this->renderIndex(new UiStateDefinition(), FakeAuthorization::denying('create'), rows: 0);

        self::assertStringNotContainsString(self::CREATE, $html);
    }

    public function test_index_when_capability_denies_create_and_empty_hides_empty_state_cta(): void
    {
        $definition = new UiStateDefinition(EntityCapabilities::all()->without(Capability::Create));

        $html = $this->renderIndex($definition, new FakeAuthorization(['*']), rows: 0);

        self::assertStringNotContainsString(self::CREATE, $html);
    }

    public function test_index_when_authz_denies_update_hides_edit_link(): void
    {
        $html = $this->renderIndex(new UiStateDefinition(), FakeAuthorization::denying('update'));

        self::assertStringNotContainsString('/ui_thing/5/edit', $html);
        self::assertStringContainsString(self::CREATE, $html);
    }

    public function test_index_when_authz_denies_delete_hides_row_delete_and_bulk_delete(): void
    {
        $html = $this->renderIndex(new UiStateDefinition(), FakeAuthorization::denying('delete'));

        self::assertStringNotContainsString('name="_method" value="DELETE"', $html);
        self::assertStringNotContainsString(self::BULK, $html);
    }

    public function test_show_when_read_only_hides_edit_and_delete_controls(): void
    {
        $html = $this->renderShow(new UiStateDefinition(EntityCapabilities::readOnly()), new FakeAuthorization(['*']));

        self::assertStringNotContainsString('/ui_thing/5/edit', $html);
        self::assertStringNotContainsString('name="_method" value="DELETE"', $html);
    }

    public function test_show_when_authz_denies_update_hides_edit_even_if_capability_allows(): void
    {
        $html = $this->renderShow(new UiStateDefinition(), FakeAuthorization::denying('update'));

        self::assertStringNotContainsString('/ui_thing/5/edit', $html);
        self::assertStringContainsString('name="_method" value="DELETE"', $html);
    }

    public function test_show_when_authz_denies_delete_hides_delete_even_if_capability_allows(): void
    {
        $html = $this->renderShow(new UiStateDefinition(), FakeAuthorization::denying('delete'));

        self::assertStringNotContainsString('name="_method" value="DELETE"', $html);
        self::assertStringContainsString('/ui_thing/5/edit', $html);
    }

    public function test_show_with_everything_allowed_renders_edit_and_delete(): void
    {
        $html = $this->renderShow(new UiStateDefinition(), new FakeAuthorization(['*']));

        self::assertStringContainsString('/ui_thing/5/edit', $html);
        self::assertStringContainsString('name="_method" value="DELETE"', $html);
    }
}
