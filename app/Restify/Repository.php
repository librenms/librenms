<?php

namespace App\Restify;

use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Binaryk\LaravelRestify\Repositories\Repository as RestifyRepository;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * Base class for v1 API model repositories.
 *
 * Restify authorizes through its own policy method names (allowRestify, show, store).
 * This maps them onto the abilities LibreNMS policies already define, so each model
 * keeps a single policy shared by the web UI and the API:
 *
 * - using the repository at all → viewAny
 * - showing a record → view
 * - storing a record → create
 *
 * update and delete already share their names. Any other ability, such as Restify's
 * bulk operations, attach, detach and sync, is denied unless the policy defines it.
 *
 * The JSON:API type of a resource is its endpoint name.
 *
 * Row visibility is not handled here: a repository whose records are not all visible
 * to every user with viewAny must restrict them in indexQuery() and showQuery().
 * mainQuery() also scopes update and delete.
 */
abstract class Repository extends RestifyRepository
{
    public static function authorizedToUseRepository(Request $request): bool
    {
        return static::authorizable() && Gate::check('viewAny', static::guessModelClassName());
    }

    /**
     * @throws AuthorizationException
     */
    public function authorizeToShow(Request $request): void
    {
        $this->authorizeTo($request, 'view');
    }

    public function authorizedToShow(Request $request): bool
    {
        return $this->authorizedTo($request, 'view');
    }

    /**
     * Only abilities defined on the policy are allowed. Restify falls back to Gate::check()
     * for other abilities, which Gate::before() would grant to every admin.
     *
     * @param  iterable<string>|string  $ability
     */
    public function authorizedTo(Request $request, iterable|string $ability): bool
    {
        $policy = Gate::getPolicyFor(static::guessModelClassName());
        if ($policy === null) {
            return false;
        }

        foreach (is_string($ability) ? [$ability] : $ability as $name) {
            if (! method_exists($policy, $name)) {
                return false;
            }
        }

        return parent::authorizedTo($request, $ability);
    }

    public static function authorizedToStore(Request $request): bool
    {
        return static::authorizable() && Gate::check('create', static::guessModelClassName());
    }

    protected function getType(RestifyRequest $request): ?string
    {
        return static::uriKey();
    }
}
