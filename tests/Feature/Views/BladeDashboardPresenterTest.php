<?php

declare(strict_types=1);

namespace BlackParadise\LaravelAdminBladeUI\Tests\Feature\Views;

use BlackParadise\LaravelAdminBladeUI\Http\Presenters\BladeDashboardPresenter;
use BlackParadise\LaravelAdminBladeUI\Tests\TestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Feature tests for BladeDashboardPresenter.
 *
 * Covers the single public method — index() — by asserting it returns
 * a 200 OK view response when given an empty entities list. The view
 * itself depends on the auth() helper, so we authenticate via actingAs.
 */
final class BladeDashboardPresenterTest extends TestCase
{
    public function test_index_returns_view_response_with_empty_entities(): void
    {
        // The dashboard view renders auth()->user(); a permissive user is enough.
        $user = new \Illuminate\Foundation\Auth\User();
        $user->id = 1;
        $user->name = 'Test Admin';
        $user->email = 'admin@example.com';
        $this->actingAs($user);

        $presenter = new BladeDashboardPresenter();

        $response = $presenter->index([]);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());

        // Body-fragment assertions prove the dashboard view actually rendered
        // (not a coincidental 200 from a fallback). 'Welcome back to' is
        // unique hero copy on the dashboard page; 'Test Admin' confirms the
        // authenticated user's name was injected into the greeting.
        $body = (string) $response->getContent();
        self::assertStringContainsString('Welcome back to', $body);
        self::assertStringContainsString('Test Admin', $body);
    }
}
