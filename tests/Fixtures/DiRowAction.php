<?php

declare(strict_types=1);

namespace BlackParadise\LaravelAdminBladeUI\Tests\Fixtures;

use BlackParadise\CoreAdmin\Domain\Action\ActionContext;
use BlackParadise\CoreAdmin\Domain\Action\ActionResult;
use BlackParadise\CoreAdmin\Domain\Action\ActionScope;
use BlackParadise\CoreAdmin\Domain\Contracts\Action\ActionContract;

/** Real class-string action (Row + Bulk) with a constructor dependency. */
final class DiRowAction implements ActionContract
{
    public function __construct(private readonly ActionGreeter $greeter) {}

    public function name(): string { return 'di_row'; }

    public function label(): string { return $this->greeter->greet() . ' row'; }

    public function scopes(): array { return [ActionScope::Row, ActionScope::Bulk]; }

    public function permission(): ?string { return null; }

    public function confirm(): bool { return false; }

    public function meta(): array { return []; }

    public function fields(): array { return []; }

    public function handle(ActionContext $context): ActionResult { return ActionResult::message('ok'); }
}
