<?php

namespace App\Restify;

use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * Restricts a Restify repository's index/show queries to records the
 * authenticated user can see, based on **port-level** access control.
 *
 * The wrapped model must expose a `scopeHasAccess(User $user)` Eloquent
 * scope that delegates to `BaseModel::hasPortAccess` typically by
 * extending `App\Models\PortRelatedModel`, or `Port` itself.
 *
 * Port access is broader than device access: it includes records on any
 * port the user has been explicitly granted, **plus** records on any
 * device the user can see.
 */
trait PortScopedRepository
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
