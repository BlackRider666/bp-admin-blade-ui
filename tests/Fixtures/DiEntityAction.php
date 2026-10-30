<?php

declare(strict_types=1);

namespace BlackParadise\LaravelAdminBladeUI\Tests\Fixtures;

use BlackParadise\CoreAdmin\Domain\Action\ActionContext;
use BlackParadise\CoreAdmin\Domain\Action\ActionResult;
use BlackParadise\CoreAdmin\Domain\Action\ActionScope;
use BlackParadise\CoreAdmin\Domain\Contracts\Action\ActionContract;

/** Real class-string action (Entity) with a constructor dependency. */
final class DiEntityAction implements ActionContract
{
    public function __construct(private readonly ActionGreeter $greeter) {}

    public function name(): string { return 'di_entity'; }

    public function label(): string { return $this->greeter->greet() . ' entity'; }

    public function scopes(): array { return [ActionScope::Entity]; }

    public function permission(): ?string { return null; }

    public function confirm(): bool { return false; }

    public function meta(): array { return []; }

    public function fields(): array { return []; }

    public function handle(ActionContext $context): ActionResult { return ActionResult::message('ok'); }
}
