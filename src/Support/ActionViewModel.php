<?php

declare(strict_types=1);

namespace BlackParadise\LaravelAdminBladeUI\Support;

use BlackParadise\CoreAdmin\Domain\Contracts\Fields\FieldContract;

/**
 * Render-ready projection of an ActionContract. `url` is the entity/bulk endpoint;
 * row-scoped views build the per-record URL via route('bpadmin.entity.action.row').
 */
final readonly class ActionViewModel
{
    /**
     * @param list<FieldContract> $fields
     */
    public function __construct(
        public string $name,
        public string $label,
        public bool $confirm,
        public bool $hasForm,
        public string $url,
        public array $fields = [],
    ) {}
}
