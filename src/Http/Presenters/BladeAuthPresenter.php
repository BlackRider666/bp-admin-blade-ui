<?php

declare(strict_types=1);

namespace BlackParadise\LaravelAdminBladeUI\Http\Presenters;

use BlackParadise\LaravelAdmin\Http\Presenters\AuthPresenterInterface;
use Symfony\Component\HttpFoundation\Response;

final class BladeAuthPresenter implements AuthPresenterInterface
{
    public function showLoginForm(): Response
    {
        return response()->view('bpadmin::pages.login');
    }

    public function loginSuccess(): Response
    {
        return to_route('bpadmin.dashboard');
    }

    public function loginFailure(): Response
    {
        return back()->withErrors(['email' => 'Invalid credentials.'])->withInput();
    }

    public function logoutSuccess(): Response
    {
        return to_route('bpadmin.auth.login');
    }
}
