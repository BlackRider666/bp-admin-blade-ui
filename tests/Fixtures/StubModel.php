<?php

declare(strict_types=1);

namespace BlackParadise\LaravelAdminBladeUI\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

/**
 * Placeholder Eloquent model referenced by StubEntityDefinition.
 *
 * Presenter tests do not perform persistence; this exists only so
 * the EntityDefinition::$model property type-checks.
 */
final class StubModel extends Model
{
    protected $table = 'stub_models';

    protected $guarded = [];
}
