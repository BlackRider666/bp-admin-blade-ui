<?php

declare(strict_types=1);

namespace BlackParadise\LaravelAdminBladeUI\Tests\Feature\Views;

use BlackParadise\CoreAdmin\Domain\Action\ActionOutcome;
use BlackParadise\CoreAdmin\Domain\Action\ActionResult;
use BlackParadise\CoreAdmin\Domain\Contracts\Action\DownloadContract;
use BlackParadise\CoreAdmin\Domain\Contracts\EntityDefinition\EntityDefinitionContract;
use BlackParadise\CoreAdmin\Domain\ValueObjects\EntityKey;
use BlackParadise\LaravelAdminBladeUI\Http\Presenters\BladeEntityPresenter;
use BlackParadise\LaravelAdminBladeUI\Tests\Fixtures\StubEntityDefinition;
use BlackParadise\LaravelAdminBladeUI\Tests\TestCase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionUnionType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * A15 (B13): BladeEntityPresenter must implement actionResult().
 *
 * Currently BladeEntityPresenter does NOT declare actionResult(), so PHP fatals
 * when the class is instantiated (abstract method not implemented — the interface
 * declares it). This test is RED via fatal/error until the impl phase adds the method.
 *
 * The expected post-fix behaviour:
 *   - returns a redirect back() with HTTP 302
 *   - flashes a 'success' key in the session
 *   - when $rowId is provided, the message includes "(#<rowId>)"
 *   - when $rowId is null, the message is the bare $message string
 */
final class BladeEntityPresenterActionResultTest extends TestCase
{
    private BladeEntityPresenter $presenter;

    protected function setUp(): void
    {
        parent::setUp();

        // Instantiating BladeEntityPresenter currently triggers a PHP fatal because
        // the interface declares actionResult() but the class does not implement it.
        // This constructor call is intentionally here so the test errors immediately
        // on that fatal — giving an honest RED before the impl phase adds the method.
        $this->presenter = new BladeEntityPresenter();

        $request = Request::create('/admin/stub_entity/1/action', 'POST');
        $request->setLaravelSession($this->app['session']->driver());
        $this->app->instance('request', $request);
    }

    // ---- A38 (Task 4.4): ActionResult branch of the widened signature ----

    public function test_action_result_message_flashes_toast_at_outcome_level(): void
    {
        $response = $this->callWithActionResult(ActionResult::message('Heads up.', ActionOutcome::Info));

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('Heads up.', $response->getSession()->get('info'));
    }

    public function test_action_result_error_outcome_flashes_error_toast(): void
    {
        $response = $this->callWithActionResult(ActionResult::message('Boom.', ActionOutcome::Error));

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('Boom.', $response->getSession()->get('error'));
    }

    public function test_action_result_success_message_does_not_flash_other_levels(): void
    {
        $response = $this->callWithActionResult(ActionResult::message('Done.'));

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('Done.', $response->getSession()->get('success'));
        self::assertNull($response->getSession()->get('error'));
    }

    public function test_action_result_redirect_navigates_to_url(): void
    {
        $response = $this->callWithActionResult(ActionResult::redirect('/admin/elsewhere'));

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertStringEndsWith('/admin/elsewhere', $response->getTargetUrl());
    }

    public function test_action_result_redirect_with_message_also_flashes_toast(): void
    {
        $response = $this->callWithActionResult(ActionResult::redirect('/admin/elsewhere', 'Moved.'));

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame('Moved.', $response->getSession()->get('success'));
    }

    public function test_action_result_download_returns_streamed_response_with_name_and_mime(): void
    {
        $response = $this->callWithActionResult(ActionResult::download($this->makeDownload()));

        self::assertInstanceOf(StreamedResponse::class, $response);
        self::assertStringContainsString('report.csv', (string) $response->headers->get('Content-Disposition'));
        self::assertStringContainsString('text/csv', (string) $response->headers->get('Content-Type'));
    }

    public function test_action_result_download_streams_the_body(): void
    {
        $response = $this->callWithActionResult(ActionResult::download($this->makeDownload()));

        self::assertInstanceOf(StreamedResponse::class, $response);
        ob_start();
        $response->sendContent();
        self::assertSame('a,b', ob_get_clean());
    }

    public function test_action_result_failed_keys_flash_warning_toast_listing_them(): void
    {
        $this->useFailedForTranslation();
        $result = ActionResult::message('Partially done.')
            ->withFailedKeys([new EntityKey(5), new EntityKey(7)]);

        $response = $this->callWithActionResult($result);

        self::assertInstanceOf(RedirectResponse::class, $response);
        $warning = (string) $response->getSession()->get('warning');
        self::assertStringContainsString('5', $warning);
        self::assertStringContainsString('7', $warning);
    }

    // ---- Task 4.4 fix round 1 (I-1, I-2, M-1, M-3, M-4) ----

    public function test_warning_outcome_with_message_and_failed_keys_keeps_message_and_keys_in_one_warning_toast(): void
    {
        $this->useFailedForTranslation();
        $result = ActionResult::message('Partially done.', ActionOutcome::Warning)
            ->withFailedKeys([new EntityKey(3), new EntityKey(7)]);

        $response = $this->callWithActionResult($result);

        $warning = $response->getSession()->get('warning');
        self::assertIsString($warning, 'Exactly one string warning toast expected (no array, no overwrite).');
        self::assertStringContainsString('Partially done.', $warning);
        self::assertStringContainsString('3, 7', $warning);
    }

    public function test_failed_keys_text_comes_from_bpadmin_actions_failed_for_translation(): void
    {
        $this->useFailedForTranslation();
        $result = ActionResult::message('Partially done.')
            ->withFailedKeys([new EntityKey(5), new EntityKey(7)]);

        $response = $this->callWithActionResult($result);

        $warning = (string) $response->getSession()->get('warning');
        self::assertStringContainsString('ZZ_FAILED[5, 7]', $warning);
        self::assertStringNotContainsString('Failed for:', $warning);
    }

    public function test_failed_keys_without_message_uses_translation_and_is_single_warning(): void
    {
        $this->useFailedForTranslation();
        $result = (new ActionResult())->withFailedKeys([new EntityKey(9)]);

        $response = $this->callWithActionResult($result);

        self::assertSame('ZZ_FAILED[9]', $response->getSession()->get('warning'));
    }

    public function test_failed_keys_list_is_truncated_to_first_ten_with_localized_more_suffix(): void
    {
        $this->useFailedForTranslation();
        $keys = array_map(static fn(int $i): EntityKey => new EntityKey(1000 + $i), range(1, 50));
        $result = ActionResult::message('Bulk done.', ActionOutcome::Warning)->withFailedKeys($keys);

        $response = $this->callWithActionResult($result);

        $warning = (string) $response->getSession()->get('warning');
        self::assertStringContainsString('1001', $warning);
        self::assertStringContainsString('1010', $warning);
        self::assertStringNotContainsString('1011', $warning);
        self::assertStringNotContainsString('1050', $warning);
        self::assertStringContainsString('ZZ_MORE[40]', $warning);
    }

    public function test_failed_keys_list_of_exactly_ten_is_not_truncated(): void
    {
        $this->useFailedForTranslation();
        $keys = array_map(static fn(int $i): EntityKey => new EntityKey(2000 + $i), range(1, 10));

        $response = $this->callWithActionResult((new ActionResult())->withFailedKeys($keys));

        $warning = (string) $response->getSession()->get('warning');
        self::assertStringContainsString('2010', $warning);
        self::assertStringNotContainsString('ZZ_MORE', $warning);
    }

    public function test_download_with_slashes_in_filename_does_not_throw(): void
    {
        $response = $this->callWithActionResult(
            ActionResult::download($this->makeDownload('../../etc/pass\\wd.csv')),
        );

        self::assertInstanceOf(StreamedResponse::class, $response);
    }

    public function test_download_filename_is_sanitized_to_basename_in_content_disposition(): void
    {
        $response = $this->callWithActionResult(
            ActionResult::download($this->makeDownload('../../etc/pass\\wd.csv')),
        );

        $disposition = (string) $response->headers->get('Content-Disposition');
        self::assertStringContainsString('wd.csv', $disposition);
        self::assertStringNotContainsString('/', $disposition);
        self::assertStringNotContainsString('\\', $disposition);
        self::assertStringNotContainsString('etc', $disposition);
    }

    public function test_download_with_message_flashes_message_then_streams(): void
    {
        $result = new ActionResult(
            message: 'Export ready.',
            outcome: ActionOutcome::Info,
            download: $this->makeDownload(),
        );

        $response = $this->callWithActionResult($result);

        self::assertInstanceOf(StreamedResponse::class, $response);
        self::assertSame('Export ready.', session('info'));
    }

    // ---- Task 4.8: narrowed signature, download warning, no English fallback ----

    public function test_action_result_first_parameter_is_exactly_action_result(): void
    {
        $type = (new ReflectionMethod(BladeEntityPresenter::class, 'actionResult'))->getParameters()[0]->getType();

        self::assertInstanceOf(ReflectionNamedType::class, $type, 'Legacy |string union must be dropped.');
        self::assertSame(ActionResult::class, $type->getName());
    }

    public function test_action_result_second_parameter_is_entity_definition_contract_only(): void
    {
        $type = (new ReflectionMethod(BladeEntityPresenter::class, 'actionResult'))->getParameters()[1]->getType();

        self::assertInstanceOf(ReflectionNamedType::class, $type, 'Second param must not be a string|null union.');
        self::assertSame(EntityDefinitionContract::class, $type->getName());
        self::assertFalse($type->allowsNull());
    }

    public function test_download_with_failed_keys_flashes_warning_and_still_streams(): void
    {
        $this->useFailedForTranslation();
        $result = ActionResult::download($this->makeDownload())
            ->withFailedKeys([new EntityKey(5), new EntityKey(7)]);

        $response = $this->callWithActionResult($result);

        self::assertInstanceOf(StreamedResponse::class, $response);
        self::assertSame('ZZ_FAILED[5, 7]', session('warning'));
    }

    public function test_failed_keys_toast_uses_real_uk_lang_keys_not_fallback(): void
    {
        $this->app->setLocale('uk');
        $expected = __('bpadmin::actions.failed_for', ['keys' => '5']);
        self::assertNotSame('bpadmin::actions.failed_for', $expected, 'lang key must exist in bp-laravel-admin.');

        $response = $this->callWithActionResult((new ActionResult())->withFailedKeys([new EntityKey(5)]));

        self::assertSame($expected, $response->getSession()->get('warning'));
    }

    public function test_failed_keys_toast_has_no_hardcoded_english_fallback_when_key_missing(): void
    {
        $this->app->setLocale('xx');
        $this->app->setFallbackLocale('xx');

        $response = $this->callWithActionResult((new ActionResult())->withFailedKeys([new EntityKey(5)]));

        self::assertStringNotContainsString('Failed for:', (string) $response->getSession()->get('warning'));
    }

    public function test_toasts_view_renders_info_flash_message(): void
    {
        session()->flash('info', 'Heads up from info.');

        $html = view('bpadmin::components.toasts')->render();

        self::assertStringContainsString('Heads up from info.', $html);
        self::assertStringContainsString("'info'", $html);
    }

    private function useFailedForTranslation(): void
    {
        $this->app['translator']->addLines([
            'actions.failed_for'  => 'ZZ_FAILED[:keys]',
            'actions.failed_more' => 'ZZ_MORE[:count]',
        ], $this->app->getLocale(), 'bpadmin');
    }

    /**
     * The current signature is string-only; guard via reflection so the test
     * fails as an assertion (not a TypeError) until Task 4.4 widens it.
     */
    private function callWithActionResult(ActionResult $result): Response
    {
        $method = new ReflectionMethod(BladeEntityPresenter::class, 'actionResult');
        $type = $method->getParameters()[0]->getType();
        $names = match (true) {
            $type instanceof ReflectionUnionType => array_map(
                static fn(ReflectionNamedType $t): string => $t->getName(),
                array_values(array_filter($type->getTypes(), static fn($t): bool => $t instanceof ReflectionNamedType)),
            ),
            $type instanceof ReflectionNamedType => [$type->getName()],
            default => [],
        };
        self::assertContains(ActionResult::class, $names, 'actionResult() must accept ActionResult (A38).');

        $response = $method->invoke($this->presenter, $result, new StubEntityDefinition());
        self::assertInstanceOf(Response::class, $response);

        return $response;
    }

    private function makeDownload(string $filename = 'report.csv'): DownloadContract
    {
        return new class ($filename) implements DownloadContract {
            public function __construct(private readonly string $name) {}

            public function filename(): string
            {
                return $this->name;
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
