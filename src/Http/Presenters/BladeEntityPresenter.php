<?php

declare(strict_types=1);

namespace BlackParadise\LaravelAdminBladeUI\Http\Presenters;

use BlackParadise\CoreAdmin\Domain\Action\ActionOutcome;
use BlackParadise\CoreAdmin\Domain\Action\ActionResult;
use BlackParadise\CoreAdmin\Domain\Contracts\Action\ActionResolverContract;
use BlackParadise\CoreAdmin\Domain\Contracts\Action\DownloadContract;
use BlackParadise\CoreAdmin\Domain\Contracts\Auth\AuthorizationProviderContract;
use BlackParadise\CoreAdmin\Domain\Contracts\Entity\EntityRecordContract;
use BlackParadise\CoreAdmin\Domain\Contracts\EntityDefinition\EntityDefinitionContract;
use BlackParadise\CoreAdmin\Domain\Query\PaginatedResult;
use BlackParadise\CoreAdmin\Domain\ValueObjects\EntityKey;
use BlackParadise\LaravelAdmin\Http\Presenters\EntityPresenterInterface;
use BlackParadise\LaravelAdminBladeUI\Support\EntityUiState;
use BlackParadise\LaravelAdminBladeUI\Support\PermissiveAuthorization;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

final readonly class BladeEntityPresenter implements EntityPresenterInterface
{
    private const int FAILED_KEYS_LIMIT = 10;

    /**
     * Optional so the presenter stays constructible by hand. The null fallback (PermissiveAuthorization)
     * is for manual construction/tests only and affects UI visibility only; the production container
     * autowires the real AuthorizationProviderContract, and server-side use cases enforce authorization.
     */
    public function __construct(
        private ?AuthorizationProviderContract $authorization = null,
        private ?ActionResolverContract $actionResolver = null,
    ) {}

    private function uiState(EntityDefinitionContract $definition): EntityUiState
    {
        return EntityUiState::for(
            $definition,
            $this->authorization ?? new PermissiveAuthorization(),
            $this->actionResolver ?? resolve(ActionResolverContract::class),
        );
    }

    public function index(PaginatedResult $paginated, array $fields, EntityDefinitionContract $definition): Response
    {
        return response()->view('bpadmin::pages.index', [
            'definition' => $definition,
            'paginated'  => $paginated,
            'records'    => $paginated->items,
            'fields'     => $fields,
            'ui'         => $this->uiState($definition),
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
            'ui'         => $this->uiState($definition),
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
        if ($failedIds !== []) {
            $messages[] = 'Skipped ' . count($failedIds) . ' record(s) due to insufficient permissions.';
        }
        if ($notFoundIds !== []) {
            $messages[] = 'Skipped ' . count($notFoundIds) . ' record(s) that no longer exist.';
        }

        $redirect = to_route('bpadmin.entity.index', ['entity' => $definition->name()]);

        $flashKey = $failedIds === [] ? 'success' : 'warning';

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

    /**
     * Security: ActionResult::$redirectUrl must come from server-side action code and must
     * never carry unvalidated user input (open-redirect risk).
     */
    public function actionResult(ActionResult $result, EntityDefinitionContract $definition): Response
    {
        $flashes = $this->flashMessages($result);

        if ($result->download instanceof DownloadContract) {
            foreach ($flashes as $key => $text) {
                Session::flash($key, $text);
            }

            $download = $result->download;
            $segments = explode('/', str_replace('\\', '/', $download->filename()));
            $filename = trim(end($segments));

            return response()->streamDownload(
                static function () use ($download): void {
                    $download->stream();
                },
                $filename !== '' ? $filename : 'download',
                ['Content-Type' => $download->mimeType()],
            );
        }

        $response = $result->redirectUrl !== null ? redirect()->to($result->redirectUrl) : back();

        foreach ($flashes as $key => $text) {
            $response->with($key, $text);
        }

        return $response;
    }

    /**
     * Flash key => text. A warning outcome shares its key with the failed-keys list, so the two are
     * merged into one toast instead of the list overwriting the message.
     *
     * @return array<string, string>
     */
    private function flashMessages(ActionResult $result): array
    {
        $failedText = $result->failedKeys !== [] ? $this->failedKeysText($result->failedKeys) : null;
        $warningKey = ActionOutcome::Warning->value;
        $message    = $result->message;
        $flashes    = [];

        if ($failedText !== null && $message !== null && $result->outcome->value === $warningKey) {
            $flashes[$warningKey] = $message . ' — ' . $failedText;
            $failedText = null;
        } elseif ($message !== null) {
            $flashes[$result->outcome->value] = $message;
        }

        if ($failedText !== null) {
            $flashes[$warningKey] = $failedText;
        }

        return $flashes;
    }

    /**
     * @param list<EntityKey> $keys
     */
    private function failedKeysText(array $keys): string
    {
        $shown = array_map(
            static fn(EntityKey $k): string => (string) $k,
            array_slice($keys, 0, self::FAILED_KEYS_LIMIT),
        );
        $list = implode(', ', $shown);
        $text = __('bpadmin::actions.failed_for', ['keys' => $list]);

        $rest = count($keys) - self::FAILED_KEYS_LIMIT;
        if ($rest > 0) {
            $text .= ' ' . __('bpadmin::actions.failed_more', ['count' => $rest]);
        }

        return $text;
    }
}
