<?php

declare(strict_types=1);

namespace BlackParadise\LaravelAdminBladeUI\Tests\Fixtures;

/** Dependency injected into class-string actions to prove container (constructor DI) resolution. */
final class ActionGreeter
{
    public function greet(): string
    {
        return 'Injected label';
    }
}
