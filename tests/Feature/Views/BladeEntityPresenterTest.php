<?php

declare(strict_types=1);

namespace BlackParadise\LaravelAdminBladeUI\Tests\Feature\Views;

use BlackParadise\CoreAdmin\Domain\Entity\EntityRecord;
use BlackParadise\CoreAdmin\Domain\Query\PaginatedResult;
use BlackParadise\LaravelAdminBladeUI\Http\Presenters\BladeEntityPresenter;
use BlackParadise\LaravelAdminBladeUI\Tests\Fixtures\StubEntityDefinition;
use BlackParadise\LaravelAdminBladeUI\Tests\TestCase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Feature tests for BladeEntityPresenter.
 *
 * Exercises every public method of the presenter except the heaviest
 * view-rendering paths (index/show/edit), which require many UI
 * subdependencies. The store/update/destroy/validationError/unauthorized/
 * notFound branches plus a happy-path create render provide ample
 * statement coverage above the 60% threshold.
 */
final class BladeEntityPresenterTest extends TestCase
{
    private BladeEntityPresenter $presenter;
    private StubEntityDefinition $definition;

    protected function setUp(): void
    {
        parent::setUp();

        $this->presenter = new BladeEntityPresenter();
        $this->definition = new StubEntityDefinition();

        // Replace the bound request with a real one so back()/withErrors()
        // / route() helpers all have session + previous URL.
        $request = Request::create('/admin/stub_entity', 'GET');
        $request->setLaravelSession($this->app['session']->driver());
        $this->app->instance('request', $request);
    }

    // ------------------------------------------------------------------
    // store / update / destroy — pure redirect responses
    // ------------------------------------------------------------------

    public function test_store_redirects_to_index_with_success_flash(): void
    {
        $created = new EntityRecord($this->definition, ['id' => 7, 'name' => 'Acme', 'email' => 'a@b']);

        $response = $this->presenter->store($created, $this->definition);

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame(
            route('bpadmin.entity.index', ['entity' => $this->definition->name()]),
            $response->getTargetUrl(),
        );
        self::assertSame(
            "{$this->definition->label()} created.",
            $response->getSession()->get('success'),
        );
    }

    public function test_update_redirects_to_show_with_success_flash(): void
    {
        $updated = new EntityRecord($this->definition, ['id' => 42, 'name' => 'Updated']);

        $response = $this->presenter->update($updated, $this->definition, '42');

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame(
            route('bpadmin.entity.show', ['entity' => $this->definition->name(), 'id' => '42']),
            $response->getTargetUrl(),
        );
        self::assertSame(
            "{$this->definition->label()} updated.",
            $response->getSession()->get('success'),
        );
    }

    public function test_destroy_redirects_to_index_with_success_flash(): void
    {
        $response = $this->presenter->destroy($this->definition, '5');

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame(
            route('bpadmin.entity.index', ['entity' => $this->definition->name()]),
            $response->getTargetUrl(),
        );
        self::assertSame(
            "{$this->definition->label()} deleted.",
            $response->getSession()->get('success'),
        );
    }

    // ------------------------------------------------------------------
    // validationError — back()->withErrors()->withInput()
    // ------------------------------------------------------------------

    public function test_validation_error_redirects_back_with_errors(): void
    {
        $response = $this->presenter->validationError([
            'name'  => ['The name field is required.'],
            'email' => ['The email field is invalid.'],
        ]);

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame(Response::HTTP_FOUND, $response->getStatusCode());

        $errors = $response->getSession()->get('errors');
        self::assertNotNull($errors);
        self::assertTrue($errors->has('name'));
        self::assertTrue($errors->has('email'));
    }

    // ------------------------------------------------------------------
    // unauthorized / notFound — abort()
    // ------------------------------------------------------------------

    public function test_unauthorized_aborts_with_403(): void
    {
        $this->expectException(HttpException::class);

        try {
            $this->presenter->unauthorized();
        } catch (HttpException $e) {
            self::assertSame(Response::HTTP_FORBIDDEN, $e->getStatusCode());
            throw $e;
        }
    }

    public function test_not_found_aborts_with_404(): void
    {
        $this->expectException(NotFoundHttpException::class);

        try {
            $this->presenter->notFound();
        } catch (NotFoundHttpException $e) {
            self::assertSame(Response::HTTP_NOT_FOUND, $e->getStatusCode());
            throw $e;
        }
    }

    // ------------------------------------------------------------------
    // create — exercises private populateRelationOptions() (no relation
    // fields → loop body skipped) and triggers view render.
    // ------------------------------------------------------------------

    public function test_create_renders_view_when_no_relation_fields_present(): void
    {
        $response = $this->presenter->create($this->definition->fields(), $this->definition);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());

        // Body-fragment assertions: the create view must render a <form>
        // posting to the entity's store route. This catches both "wrong
        // view rendered" and "definition not passed" regressions.
        $body = (string) $response->getContent();
        self::assertStringContainsString('<form', $body);
        self::assertStringContainsString(
            route('bpadmin.entity.store', ['entity' => $this->definition->name()]),
            $body,
        );
    }

    // ------------------------------------------------------------------
    // index — happy path with empty paginated result
    // ------------------------------------------------------------------

    public function test_index_renders_view_with_empty_paginated_result(): void
    {
        $paginated = new PaginatedResult(items: [], total: 0, page: 1, perPage: 15);

        $response = $this->presenter->index($paginated, $this->definition->fields(), $this->definition);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());

        // Body-fragment assertions: the index view must render the entity's
        // descriptive heading copy ('Browse, search and manage') and the
        // raw entity name from $definition->name(). Together they confirm
        // the right view was rendered AND the definition was passed through.
        $body = (string) $response->getContent();
        self::assertStringContainsString('Browse, search and manage', $body);
        self::assertStringContainsString($this->definition->name(), $body);
    }

    // ------------------------------------------------------------------
    // show / edit — render record-detail views
    // ------------------------------------------------------------------

    public function test_show_renders_view_for_existing_record(): void
    {
        $record = new EntityRecord($this->definition, [
            'id'    => 1,
            'name'  => 'Acme',
            'email' => 'acme@example.com',
        ]);

        $response = $this->presenter->show($record, $this->definition->fields(), $this->definition);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());

        // Body-fragment assertions: the show view renders each field's value
        // via field-display (default branch -> text). Asserting both seeded
        // attribute values confirms the EntityRecord data flowed all the way
        // through the view, not just that *some* page rendered.
        $body = (string) $response->getContent();
        self::assertStringContainsString('Acme', $body);
        self::assertStringContainsString('acme@example.com', $body);
    }

    public function test_edit_renders_view_for_existing_record(): void
    {
        $record = new EntityRecord($this->definition, [
            'id'    => 1,
            'name'  => 'Acme',
            'email' => 'acme@example.com',
        ]);

        $response = $this->presenter->edit($record, $this->definition->fields(), $this->definition);

        self::assertSame(Response::HTTP_OK, $response->getStatusCode());

        // Body-fragment assertions: edit = "show + form". The view must
        // render a <form> posting to the update route AND pre-fill the
        // existing record's name attribute into a form input. These two
        // assertions together catch both "wrong view rendered" and "record
        // values not passed to inputs" regressions.
        $body = (string) $response->getContent();
        self::assertStringContainsString('<form', $body);
        self::assertStringContainsString(
            route('bpadmin.entity.update', ['entity' => $this->definition->name(), 'id' => $record->id()]),
            $body,
        );
        self::assertStringContainsString('Acme', $body);
    }
}
