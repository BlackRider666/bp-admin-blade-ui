<?php

declare(strict_types=1);

namespace BlackParadise\LaravelAdminBladeUI\Tests\Fixtures;

use BlackParadise\CoreAdmin\Domain\Contracts\Auth\AuthorizationProviderContract;
use BlackParadise\CoreAdmin\Domain\Contracts\EntityDefinition\EntityDefinitionContract;

/** Allow-list authorization double: everything not listed is denied; '*' allows all. */
final class FakeAuthorization implements AuthorizationProviderContract
{
    /** @param list<string> $allowed */
    public function __construct(private readonly array $allowed = ['*']) {}

    public static function denying(string ...$abilities): self
    {
        return new self(['*', ...array_map(static fn(string $a): string => '!' . $a, $abilities)]);
    }

    public function can(string $action, EntityDefinitionContract $entityDefinition): bool
    {
        if (in_array('!' . $action, $this->allowed, true)) {
            return false;
        }

        return in_array('*', $this->allowed, true) || in_array($action, $this->allowed, true);
    }
}
