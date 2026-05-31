<?php

declare(strict_types=1);

namespace BlackParadise\LaravelAdminBladeUI\Http\Presenters;

use BlackParadise\CoreAdmin\Domain\Contracts\Entity\EntityRecordContract;
use BlackParadise\CoreAdmin\Domain\Contracts\EntityDefinition\EntityDefinitionContract;
use BlackParadise\CoreAdmin\Domain\Query\PaginatedResult;
use BlackParadise\LaravelAdmin\Http\Presenters\EntityPresenterInterface;
use Symfony\Component\HttpFoundation\Response;

final class BladeEntityPresenter implements EntityPresenterInterface
{
    public function index(PaginatedResult $paginated, array $fields, EntityDefinitionContract $definition): Response
    {
        return response()->view('bpadmin::pages.index', [
            'definition' => $definition,
            'paginated'  => $paginated,
            'records'    => $paginated->items,
            'fields'     => $fields,
        ]);
    }

    public function create(array $fields, EntityDefinitionContract $definition): Response
    {
        // Relation options are now populated upstream by BuildFormViewUseCase
        // via RelationOptionsProviderContract; views read $field->meta()['options'].
        return response()->view('bpadmin::pages.create', [
            'definition' => $definition,
            'fields'     => $fields,
        ]);
    }

    public function store(EntityRecordContract $created, EntityDefinitionContract $definition): Response
    {
        return to_route('bpadmin.entity.index', ['entity' => $definition->name()])
            ->with('success', "{$definition->label()} created.");
    }

    public function show(EntityRecordContract $record, array $fields, EntityDefinitionContract $definition): Response
    {
        return response()->view('bpadmin::pages.show', [
            'definition' => $definition,
            'record'     => $record,
            'fields'     => $fields,
        ]);
    }

    public function edit(EntityRecordContract $record, array $fields, EntityDefinitionContract $definition): Response
    {
        return response()->view('bpadmin::pages.edit', [
            'definition' => $definition,
            'record'     => $record,
            'fields'     => $fields,
        ]);
    }

    public function update(EntityRecordContract $updated, EntityDefinitionContract $definition, string $id): Response
    {
        return to_route('bpadmin.entity.show', ['entity' => $definition->name(), 'id' => $id])
            ->with('success', "{$definition->label()} updated.");
    }

    public function destroy(EntityDefinitionContract $definition, string $id): Response
    {
        return to_route('bpadmin.entity.index', ['entity' => $definition->name()])
            ->with('success', "{$definition->label()} deleted.");
    }

    public function bulkDestroyResult(
        int $deletedCount,
        array $failedIds,
        array $notFoundIds,
        EntityDefinitionContract $definition,
    ): Response {
        $messages = [];
        if ($deletedCount > 0) {
            $messages[] = "Deleted {$deletedCount} " . $definition->label() . '.';
        }
        if (!empty($failedIds)) {
            $messages[] = 'Skipped ' . count($failedIds) . ' record(s) due to insufficient permissions.';
        }
        if (!empty($notFoundIds)) {
            $messages[] = 'Skipped ' . count($notFoundIds) . ' record(s) that no longer exist.';
        }

        $redirect = to_route('bpadmin.entity.index', ['entity' => $definition->name()]);

        $flashKey = !empty($failedIds) ? 'warning' : 'success';

        return $redirect->with($flashKey, implode(' ', $messages) ?: 'No records were deleted.');
    }

    public function unauthorized(): Response
    {
        abort(403);
    }

    public function notFound(): Response
    {
        abort(404);
    }

    public function validationError(array $errors): Response
    {
        return back()->withErrors($errors)->withInput();
    }
}
