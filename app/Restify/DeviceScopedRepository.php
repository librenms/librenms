<?php

namespace App\Restify;

use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * Restricts a Restify repository's index/show queries to records the
 * authenticated user can see, based on **device-level** access control.
 *
 * The wrapped model must expose a `scopeHasAccess(User $user)` Eloquent
 * scope — typically by extending `App\Models\DeviceRelatedModel`, or by
 * defining its own override (e.g. `Device`, `DeviceGroup`, `Location`,
 * `Bill`, `AlertRule`).
 */
trait DeviceScopedRepository
{
    public static function indexQuery(RestifyRequest $request, Builder|Relation $query)
    {
        if ($user = $request->user()) {
            return $query->hasAccess($user);
        }

        return $query->whereRaw('1 = 0');
    }

    public static function showQuery(RestifyRequest $request, Builder|Relation $query)
    {
        return static::indexQuery($request, $query);
    }
}
