<?php

declare(strict_types=1);

namespace BlackParadise\LaravelAdminBladeUI\Http\Presenters;

use BlackParadise\LaravelAdmin\Http\Presenters\DashboardPresenterInterface;
use Symfony\Component\HttpFoundation\Response;

final class BladeDashboardPresenter implements DashboardPresenterInterface
{
    public function index(array $entities): Response
    {
        return response()->view('bpadmin::pages.dashboard', [
            'entities' => $entities,
        ]);
    }
}
