<?php

declare(strict_types=1);

namespace BlackParadise\LaravelAdminBladeUI\Tests\Feature\Views;

use BlackParadise\LaravelAdminBladeUI\Http\Presenters\BladeAuthPresenter;
use BlackParadise\LaravelAdminBladeUI\Tests\TestCase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Feature tests for BladeAuthPresenter.
 *
 * Verifies HTTP-layer behaviour of each public method:
 *   - showLoginForm() returns a 200 view response.
 *   - loginSuccess() redirects to bpadmin.dashboard.
 *   - loginFailure() redirects back with the email error.
 *   - logoutSuccess() redirects to bpadmin.auth.login.
 */
final class BladeAuthPresenterTest extends TestCase
{
    private BladeAuthPresenter $presenter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->presenter = new BladeAuthPresenter();
    }

    public function test_login_success_redirects_to_dashboard_route(): void
    {
        $response = $this->presenter->loginSuccess();

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame(Response::HTTP_FOUND, $response->getStatusCode());
        self::assertSame(route('bpadmin.dashboard'), $response->getTargetUrl());
    }

    public function test_logout_success_redirects_to_login_route(): void
    {
        $response = $this->presenter->logoutSuccess();

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame(Response::HTTP_FOUND, $response->getStatusCode());
        self::assertSame(route('bpadmin.auth.login'), $response->getTargetUrl());
    }

    public function test_login_failure_redirects_back_with_email_error(): void
    {
        // back() requires session + a current request to determine previous URL.
        $request = Request::create('/admin/auth/login', 'POST');
        $request->setLaravelSession($this->app['session']->driver());
        $this->app->instance('request', $request);

        $response = $this->presenter->loginFailure();

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame(Response::HTTP_FOUND, $response->getStatusCode());

        $errors = $response->getSession()->get('errors');
        self::assertNotNull($errors);
        self::assertTrue($errors->has('email'));
        self::assertSame('Invalid credentials.', $errors->first('email'));
    }

    public function test_show_login_form_returns_view_response(): void
    {
        $response = $this->presenter->showLoginForm();

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());
        // The login form posts to bpadmin.auth.login.post — verify the
        // URL for that route is present in the rendered HTML.
        self::assertStringContainsString(
            route('bpadmin.auth.login.post'),
            (string) $response->getContent(),
        );
    }
}
