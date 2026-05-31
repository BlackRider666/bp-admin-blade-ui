<?php

declare(strict_types=1);

namespace BlackParadise\LaravelAdminBladeUI\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

/**
 * Test fixture: tags table for relation-options coverage tests.
 *
 * Used by {@see StubEntityDefinitionWithRelation} to exercise
 * {@see \BlackParadise\LaravelAdminBladeUI\Http\Presenters\BladeEntityPresenter::populateRelationOptions()}.
 */
final class Tag extends Model
{
    protected $table = 'tags';

    protected $guarded = [];

    public $timestamps = false;
}
