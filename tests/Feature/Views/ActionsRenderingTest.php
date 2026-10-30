<?php

declare(strict_types=1);

namespace BlackParadise\LaravelAdminBladeUI\Tests\Feature\Views;

use BlackParadise\CoreAdmin\Domain\Action\ActionOutcome;
use BlackParadise\CoreAdmin\Domain\Action\ActionResult;
use BlackParadise\CoreAdmin\Domain\Action\ActionScope;
use BlackParadise\CoreAdmin\Domain\Contracts\Action\DownloadContract;
use BlackParadise\CoreAdmin\Domain\Fields\TextField;
use BlackParadise\CoreAdmin\Domain\ValueObjects\EntityCapabilities;
use BlackParadise\CoreAdmin\Domain\ValueObjects\EntityKey;
use BlackParadise\LaravelAdminBladeUI\Tests\Feature\Views\Concerns\RendersUiPages;
use BlackParadise\LaravelAdminBladeUI\Tests\Fixtures\FakeAuthorization;
use BlackParadise\LaravelAdminBladeUI\Tests\Fixtures\UiStateAction;
use BlackParadise\LaravelAdminBladeUI\Tests\Fixtures\UiStateDefinition;
use BlackParadise\LaravelAdminBladeUI\Tests\TestCase;
use Illuminate\Support\Facades\View;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;

/**
 * A40 (actions UI placement/confirm/modal form) and A38 (toasts, plain-form download, warning toast).
 *
 * Markup contract the impl must provide (hooks, not styling):
 *  - row "..." dropdown wrapper carries `data-bp-row-actions`;
 *  - action forms are plain `<form method="POST" action="{action url}">` with @csrf (no fetch/AJAX submit);
 *  - bulk action forms submit selected keys as hidden `keys[]` inputs;
 *  - an action with fields() renders a modal whose inputs are named `input[<field>]`.
 */
final class ActionsRenderingTest extends TestCase
{
    use RendersUiPages;

    private function entityUrl(string $action): string
    {
        return route('bpadmin.entity.action', ['entity' => 'ui_thing', 'action' => $action]);
    }

    private function rowUrl(int $id, string $action): string
    {
        return route('bpadmin.entity.action.row', ['entity' => 'ui_thing', 'id' => $id, 'action' => $action]);
    }

    // ------------------------------------------------------------------ A40: entity actions

    public function test_index_renders_authorized_entity_action_as_post_form(): void
    {
        $definition = new UiStateDefinition(EntityCapabilities::all(), [new UiStateAction('export', [ActionScope::Entity])]);

        $html = $this->renderIndex($definition, new FakeAuthorization(['*']));

        self::assertStringContainsString('action="' . $this->entityUrl('export') . '"', $html);
        self::assertStringContainsString('Label export', $html);
    }

    public function test_index_places_entity_action_next_to_create_before_the_table(): void
    {
        $definition = new UiStateDefinition(EntityCapabilities::all(), [new UiStateAction('export', [ActionScope::Entity])]);

        $html = $this->renderIndex($definition, new FakeAuthorization(['*']));

        $actionPos = strpos($html, $this->entityUrl('export'));
        $createPos = strpos($html, 'data-bp-new');
        $tablePos  = strpos($html, '<table');
        self::assertIsInt($actionPos);
        self::assertIsInt($createPos);
        self::assertIsInt($tablePos);
        self::assertLessThan($tablePos, $actionPos);
        self::assertLessThan(600, abs($actionPos - $createPos), 'entity action must sit next to the Create control');
    }

    public function test_index_omits_entity_action_when_not_authorized(): void
    {
        $definition = new UiStateDefinition(EntityCapabilities::all(), [new UiStateAction('export', [ActionScope::Entity])]);

        $html = $this->renderIndex($definition, FakeAuthorization::denying('action.export'));

        self::assertStringNotContainsString($this->entityUrl('export'), $html);
    }

    public function test_index_renders_entity_action_on_read_only_definition_when_authorized(): void
    {
        $definition = new UiStateDefinition(EntityCapabilities::readOnly(), [new UiStateAction('export', [ActionScope::Entity])]);

        $html = $this->renderIndex($definition, new FakeAuthorization(['*']));

        self::assertStringContainsString($this->entityUrl('export'), $html);
    }

    // ------------------------------------------------------------------ A40: bulk actions

    public function test_index_renders_bulk_action_in_bulk_bar_with_keys_array_input(): void
    {
        $definition = new UiStateDefinition(EntityCapabilities::readOnly(), [new UiStateAction('archive', [ActionScope::Bulk])]);

        $html = $this->renderIndex($definition, new FakeAuthorization(['*']));

        self::assertStringContainsString('action="' . $this->entityUrl('archive') . '"', $html);
        self::assertStringContainsString('name="keys[]"', $html);
    }

    public function test_index_keeps_bulk_selection_when_only_bulk_action_present_on_read_only(): void
    {
        $definition = new UiStateDefinition(EntityCapabilities::readOnly(), [new UiStateAction('archive', [ActionScope::Bulk])]);

        $html = $this->renderIndex($definition, new FakeAuthorization(['*']));

        self::assertStringContainsString('bpBulkSelect', $html);
        self::assertStringNotContainsString('/ui_thing/bulk-destroy', $html);
    }

    public function test_index_omits_bulk_action_when_not_authorized(): void
    {
        $definition = new UiStateDefinition(EntityCapabilities::all(), [new UiStateAction('archive', [ActionScope::Bulk])]);

        $html = $this->renderIndex($definition, FakeAuthorization::denying('action.archive'));

        self::assertStringNotContainsString($this->entityUrl('archive'), $html);
    }

    // ------------------------------------------------------------------ A40: row actions

    public function test_index_renders_row_action_in_row_dropdown_posting_to_row_endpoint(): void
    {
        $definition = new UiStateDefinition(EntityCapabilities::readOnly(), [new UiStateAction('archive', [ActionScope::Row])]);

        $html = $this->renderIndex($definition, new FakeAuthorization(['*']));

        self::assertStringContainsString('data-bp-row-actions', $html);
        self::assertStringContainsString('action="' . $this->rowUrl(5, 'archive') . '"', $html);
    }

    public function test_index_renders_row_action_per_record(): void
    {
        $definition = new UiStateDefinition(EntityCapabilities::readOnly(), [new UiStateAction('archive', [ActionScope::Row])]);

        $html = $this->renderIndex($definition, new FakeAuthorization(['*']), rows: 2);

        self::assertStringContainsString($this->rowUrl(5, 'archive'), $html);
        self::assertStringContainsString($this->rowUrl(6, 'archive'), $html);
    }

    public function test_index_omits_row_dropdown_when_no_authorized_row_actions(): void
    {
        $definition = new UiStateDefinition(EntityCapabilities::all(), [new UiStateAction('archive', [ActionScope::Row])]);

        $html = $this->renderIndex($definition, FakeAuthorization::denying('action.archive'));

        self::assertStringNotContainsString('data-bp-row-actions', $html);
        self::assertStringNotContainsString($this->rowUrl(5, 'archive'), $html);
    }

    // ------------------------------------------------------------------ A40: show header buttons

    public function test_show_renders_row_action_as_header_button_form(): void
    {
        $definition = new UiStateDefinition(EntityCapabilities::readOnly(), [new UiStateAction('archive', [ActionScope::Row])]);

        $html = $this->renderShow($definition, new FakeAuthorization(['*']));

        self::assertStringContainsString('action="' . $this->rowUrl(5, 'archive') . '"', $html);
        self::assertStringContainsString('Label archive', $html);
    }

    public function test_show_omits_row_action_when_not_authorized(): void
    {
        $definition = new UiStateDefinition(EntityCapabilities::all(), [new UiStateAction('archive', [ActionScope::Row])]);

        $html = $this->renderShow($definition, FakeAuthorization::denying('action.archive'));

        self::assertStringNotContainsString($this->rowUrl(5, 'archive'), $html);
    }

    // ------------------------------------------------------------------ A40: confirm

    public function test_action_with_confirm_asks_through_confirm_store_before_submit(): void
    {
        $definition = new UiStateDefinition(EntityCapabilities::readOnly(), [
            new UiStateAction('purge', [ActionScope::Entity], confirm: true),
        ]);

        $html = $this->renderIndex($definition, new FakeAuthorization(['*']));

        self::assertStringContainsString('$store.confirm.ask', $this->formOf($html, $this->entityUrl('purge')));
    }

    public function test_action_without_confirm_does_not_use_confirm_store(): void
    {
        $definition = new UiStateDefinition(EntityCapabilities::readOnly(), [
            new UiStateAction('ping', [ActionScope::Entity], confirm: false),
        ]);

        $html = $this->renderIndex($definition, new FakeAuthorization(['*']));

        self::assertStringNotContainsString('$store.confirm.ask', $this->formOf($html, $this->entityUrl('ping')));
    }

    /** Returns the <form ...>...</form> markup posting to $url; fails the test when there is none. */
    private function formOf(string $html, string $url): string
    {
        $found = preg_match('#<form[^>]*action="' . preg_quote($url, '#') . '".*?</form>#s', $html, $m);
        self::assertSame(1, $found, 'expected a <form> posting to ' . $url);

        return $m[0];
    }

    // ------------------------------------------------------------------ A40: modal form

    public function test_action_with_fields_renders_modal_form_with_input_array_names(): void
    {
        $definition = new UiStateDefinition(EntityCapabilities::readOnly(), [
            new UiStateAction('ban', [ActionScope::Entity], fields: [TextField::make('reason')]),
        ]);

        $html = $this->renderIndex($definition, new FakeAuthorization(['*']));

        self::assertStringContainsString('name="input[reason]"', $html);
        self::assertStringContainsString('action="' . $this->entityUrl('ban') . '"', $html);
    }

    public function test_action_without_fields_renders_no_input_array_fields(): void
    {
        $definition = new UiStateDefinition(EntityCapabilities::readOnly(), [new UiStateAction('ping', [ActionScope::Entity])]);

        $html = $this->renderIndex($definition, new FakeAuthorization(['*']));

        self::assertStringNotContainsString('name="input[', $html);
    }

    public function test_action_modal_reopens_with_old_input_and_validation_errors(): void
    {
        $definition = new UiStateDefinition(EntityCapabilities::readOnly(), [
            new UiStateAction('ban', [ActionScope::Entity], fields: [TextField::make('reason')]),
        ]);
        session()->flashInput(['input' => ['reason' => 'typed-before-failure']]);
        View::share('errors', new ViewErrorBag());
        View::shared('errors')->put('default', new MessageBag(['input.reason' => ['Reason is too short.']]));

        $html = $this->renderIndex($definition, new FakeAuthorization(['*']));

        self::assertStringContainsString('typed-before-failure', $html);
        self::assertStringContainsString('Reason is too short.', $html);
    }

    // ------------------------------------------------------------------ A38: toasts / download / warning

    public function test_action_forms_submit_as_plain_posts_with_csrf_token(): void
    {
        $definition = new UiStateDefinition(EntityCapabilities::readOnly(), [new UiStateAction('export', [ActionScope::Entity])]);

        $html = $this->renderIndex($definition, new FakeAuthorization(['*']));

        self::assertMatchesRegularExpression(
            '#<form[^>]*method="POST"[^>]*action="' . preg_quote($this->entityUrl('export'), '#') . '"[^>]*>\s*<input[^>]*name="_token"#',
            $html,
        );
    }

    public function test_toasts_view_renders_warning_flash_as_warning_toast(): void
    {
        session()->flash('warning', 'Export partly failed — Failed for: 5, 7');

        $html = view('bpadmin::components.toasts')->render();

        self::assertStringContainsString('Export partly failed', $html);
        self::assertStringContainsString("'warning'", $html);
    }

    public function test_download_with_warning_message_and_failed_keys_merges_into_one_warning_toast(): void
    {
        $presenter = $this->presenterWith(new FakeAuthorization(['*']));
        $result = (new ActionResult(
            message: 'Exported with problems',
            outcome: ActionOutcome::Warning,
            download: $this->download(),
        ))->withFailedKeys([new EntityKey(5), new EntityKey(7)]);

        $presenter->actionResult($result, new UiStateDefinition());

        $toast = (string) session('warning');
        self::assertStringContainsString('Exported with problems', $toast);
        self::assertStringContainsString('5', $toast);
        self::assertStringContainsString('7', $toast);
    }

    public function test_redirect_with_warning_message_and_failed_keys_merges_into_one_warning_toast(): void
    {
        $presenter = $this->presenterWith(new FakeAuthorization(['*']));
        $result = (new ActionResult(
            message: 'Done with problems',
            outcome: ActionOutcome::Warning,
        ))->withFailedKeys([new EntityKey(9)]);

        $response = $presenter->actionResult($result, new UiStateDefinition());

        $toast = (string) $response->getSession()->get('warning');
        self::assertStringContainsString('Done with problems', $toast);
        self::assertStringContainsString('9', $toast);
    }

    private function download(): DownloadContract
    {
        return new class implements DownloadContract {
            public function filename(): string
            {
                return 'report.csv';
            }

            public function mimeType(): string
            {
                return 'text/csv';
            }

            public function stream(): void
            {
                echo 'a,b';
            }
        };
    }
}
