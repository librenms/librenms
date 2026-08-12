<?php

namespace App\Restify;

use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Binaryk\LaravelRestify\Repositories\Repository as RestifyRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Base class for all v1 API model repositories.
 *
 * Access to any repository requires the global api.access permission;
 * model-specific policies control what can be done within a repository.
 */
abstract class Repository extends RestifyRepository
{
    public static function authorizedToUseRepository(Request $request): bool
    {
        $user = $request->user();

        return $user !== null && $user->can('api.access');
    }

    /**
     * Restify does not authorize index requests against the model policy by
     * itself, so gate them on viewAny here.
     */
    public static function mainQuery(RestifyRequest $request, Builder|Relation $query)
    {
        if ($request->isIndexRequest()) {
            Gate::authorize('viewAny', static::guessModelClassName());
        }

        return $query;
    }
}
