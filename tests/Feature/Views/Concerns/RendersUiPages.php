<?php

declare(strict_types=1);

namespace BlackParadise\LaravelAdminBladeUI\Tests\Feature\Views\Concerns;

use BlackParadise\CoreAdmin\Domain\Contracts\Auth\AuthorizationProviderContract;
use BlackParadise\CoreAdmin\Domain\Contracts\EntityDefinition\EntityDefinitionContract;
use BlackParadise\CoreAdmin\Domain\Entity\EntityRecord;
use BlackParadise\CoreAdmin\Domain\Query\PaginatedResult;
use BlackParadise\LaravelAdminBladeUI\Http\Presenters\BladeEntityPresenter;
use Illuminate\Http\Request;

/**
 * Renders the index/show pages through the real BladeEntityPresenter with a fake authorization provider.
 *
 * Assumption for the impl: the presenter obtains AuthorizationProviderContract through the container
 * (constructor autowiring), so binding the fake before `make()` is enough.
 */
trait RendersUiPages
{
    private function presenterWith(AuthorizationProviderContract $auth): BladeEntityPresenter
    {
        $this->app->instance(AuthorizationProviderContract::class, $auth);

        $request = Request::create('/admin/ui_thing', 'GET');
        $request->setLaravelSession($this->app['session']->driver());
        $this->app->instance('request', $request);

        return $this->app->make(BladeEntityPresenter::class);
    }

    private function renderIndex(EntityDefinitionContract $definition, AuthorizationProviderContract $auth, int $rows = 1): string
    {
        $items = [];
        for ($i = 1; $i <= $rows; $i++) {
            $items[] = new EntityRecord($definition, ['id' => $i + 4, 'title' => 'Hello ' . $i]);
        }

        $response = $this->presenterWith($auth)->index(
            new PaginatedResult($items, $rows, 1, 15),
            $definition->fields(),
            $definition,
        );

        return (string) $response->getContent();
    }

    private function renderShow(EntityDefinitionContract $definition, AuthorizationProviderContract $auth): string
    {
        $record = new EntityRecord($definition, ['id' => 5, 'title' => 'Hello']);

        $response = $this->presenterWith($auth)->show($record, $definition->fields(), $definition);

        return (string) $response->getContent();
    }
}
