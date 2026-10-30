<?php

declare(strict_types=1);

namespace BlackParadise\LaravelAdminBladeUI\Tests\Fixtures;

use BlackParadise\CoreAdmin\Domain\Action\ActionContext;
use BlackParadise\CoreAdmin\Domain\Action\ActionResult;
use BlackParadise\CoreAdmin\Domain\Action\ActionScope;
use BlackParadise\CoreAdmin\Domain\Contracts\Action\ActionContract;
use BlackParadise\CoreAdmin\Domain\Contracts\Fields\FieldContract;

/** Inline ActionContract double for UI tests. */
final class UiStateAction implements ActionContract
{
    /**
     * @param list<ActionScope> $scopes
     * @param array<FieldContract> $fields
     */
    public function __construct(
        private readonly string $name,
        private readonly array $scopes,
        private readonly ?string $permission = null,
        private readonly bool $confirm = false,
        private readonly array $fields = [],
    ) {}

    public function name(): string
    {
        return $this->name;
    }

    public function label(): string
    {
        return 'Label ' . $this->name;
    }

    public function scopes(): array
    {
        return $this->scopes;
    }

    public function permission(): ?string
    {
        return $this->permission;
    }

    public function confirm(): bool
    {
        return $this->confirm;
    }

    public function meta(): array
    {
        return [];
    }

    public function fields(): array
    {
        return $this->fields;
    }

    public function handle(ActionContext $context): ActionResult
    {
        return ActionResult::message('ok');
    }
}
