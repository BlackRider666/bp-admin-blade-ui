<?php

declare(strict_types=1);

namespace BlackParadise\LaravelAdminBladeUI\Support;

use BlackParadise\CoreAdmin\Domain\Contracts\Auth\AuthorizationProviderContract;
use BlackParadise\CoreAdmin\Domain\Contracts\EntityDefinition\EntityDefinitionContract;

/**
 * UI-only fallback used when BladeEntityPresenter is constructed by hand without an authorization
 * provider: controls then follow entity capabilities only. Server-side use cases still enforce
 * authorization; the container-built presenter always receives the real provider.
 *
 * Fallback for manual construction and tests ONLY. It merely decides which controls the UI shows
 * (it can hide, never grant); production wiring autowires the real AuthorizationProviderContract,
 * and server-side use cases enforce authorization regardless of what the UI renders.
 */
final class PermissiveAuthorization implements AuthorizationProviderContract
{
    public function can(string $action, EntityDefinitionContract $entityDefinition): bool
    {
        return true;
    }
}
