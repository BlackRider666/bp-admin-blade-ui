<?php

declare(strict_types=1);

/*
 * Architecture rule for blade-ui-next (Presentation layer):
 *
 * - Не імпортує Application use cases напряму (тільки через DI presenter contract).
 * - Не імпортує Domain Mutator/Repository напряму.
 * - Може використовувати Domain (read-only) і Infrastructure (через DI).
 *
 * Per spec §4.1.
 */

arch('presentation does not call application use cases directly')
    ->expect('BlackParadise\LaravelAdminBladeUI')
    ->not->toUse('BlackParadise\CoreAdmin\Application\UseCases');

arch('presentation does not call domain repositories or mutators directly')
    ->expect('BlackParadise\LaravelAdminBladeUI')
    ->not->toUse([
        'BlackParadise\CoreAdmin\Domain\Repositories',
        'BlackParadise\CoreAdmin\Domain\Mutators',
    ]);
