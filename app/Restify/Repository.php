<?php

namespace App\Restify;

use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Binaryk\LaravelRestify\Repositories\Repository as RestifyRepository;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

abstract class Repository extends RestifyRepository
{
    public static function authorizedToUseRepository(Request $request): bool
    {
        $user = $request->user();

        return $user !== null && $user->can('api.access');
    }

    public static function mainQuery(RestifyRequest $request, Builder|Relation $query)
    {
        if ($request->isIndexRequest()) {
            Gate::authorize('viewAny', static::guessModelClassName());
        }

        return $query;
    }

    public static function indexQuery(RestifyRequest $request, Builder|Relation $query)
    {
        return $query;
    }

    public static function showQuery(RestifyRequest $request, Builder|Relation $query)
    {
        return $query;
    }
}
