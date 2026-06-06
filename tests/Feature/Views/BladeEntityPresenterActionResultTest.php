<?php

declare(strict_types=1);

namespace BlackParadise\LaravelAdminBladeUI\Tests\Feature\Views;

use BlackParadise\LaravelAdminBladeUI\Http\Presenters\BladeEntityPresenter;
use BlackParadise\LaravelAdminBladeUI\Tests\TestCase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

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

    public function test_action_result_without_row_id_redirects_back_with_success_flash(): void
    {
        $response = $this->presenter->actionResult('Exported successfully.');

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame(Response::HTTP_FOUND, $response->getStatusCode());
        self::assertSame(
            'Exported successfully.',
            $response->getSession()->get('success'),
        );
    }

    public function test_action_result_with_row_id_appends_id_to_flash_message(): void
    {
        $response = $this->presenter->actionResult('Export dispatched.', '42');

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame(Response::HTTP_FOUND, $response->getStatusCode());
        self::assertSame(
            'Export dispatched. (#42)',
            $response->getSession()->get('success'),
        );
    }
}
