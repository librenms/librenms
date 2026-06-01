<?php

namespace App\Restify;

use Binaryk\LaravelRestify\Eager\RelatedCollection;
use Binaryk\LaravelRestify\Fields\EagerField;
use Binaryk\LaravelRestify\Http\Requests\RestifyRequest;
use Binaryk\LaravelRestify\Repositories\Repository as RestifyRepository;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use ReflectionMethod;

abstract class Repository extends RestifyRepository
{
    /**
     * Drive every relation's public name from the *owning* repository's uriKey.
     *
     * Restify resolves a nested route `/{parent}/{id}/{segment}` by matching {segment}
     * against each eager field's getAttribute() (label ?? attribute). By default that is
     * the Eloquent relation method name (e.g. `outages`), which need not equal the related
     * repository's uriKey (e.g. `device-outages`) — when they differ the endpoint 403s.
     *
     * Setting the label to the related repository's uriKey here means each model's
     * repository is the single source of truth for its public name: rename it once
     * (via uriKey) and every reference to it picks up the new segment automatically.
     * Explicit per-field ->label() calls still win (we only fill the gap).
     */
    public static function collectRelated(): RelatedCollection
    {
        return parent::collectRelated()->each(function ($field): void {
            // getAttribute() (label ?? attribute) is what Restify matches the nested-route
            // segment against, and the segment must also be a registered repository uriKey.
            // Pin it to the related repository's uriKey so the owning repository is the
            // single source of truth for the relation's public name. (No parent declares
            // two relations to the same repository, so this never collides.)
            if ($field instanceof EagerField) {
                $field->label = $field->repositoryClass::uriKey();
            }
        });
    }

    /**
     * Actions to suppress from the OpenAPI document and (by convention) from custom routes().
     * Subclasses may override with any of: 'store', 'update', 'destroy'.
     *
     * @var string[]
     */
    protected static array $disabledActions = [];

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

    /**
     * Whether the action should appear in the OpenAPI document. Combines the explicit
     * $disabledActions list, any LibreNMS-side authorizedTo<Action> override (which by
     * convention returns false unconditionally), and policy method existence.
     */
    public static function actionEnabled(string $action): bool
    {
        if (in_array($action, static::$disabledActions, true)) {
            return false;
        }

        $authMethod = match ($action) {
            'store' => 'authorizedToStore',
            'update' => 'authorizedToUpdate',
            'destroy' => 'authorizedToDelete',
            default => null,
        };

        $policyMethod = match ($action) {
            'store' => 'create',
            'update' => 'update',
            'destroy' => 'delete',
            default => null,
        };

        if ($authMethod === null || $policyMethod === null) {
            return false;
        }

        $declaringClass = (new ReflectionMethod(static::class, $authMethod))->getDeclaringClass()->getName();
        if ($declaringClass !== self::class && str_starts_with($declaringClass, 'App\\Restify\\')) {
            return false;
        }

        if (! property_exists(static::class, 'model')) {
            return false;
        }
        /** @var class-string<\Illuminate\Database\Eloquent\Model>|null $modelClass */
        $modelClass = static::$model ?? null; // @phpstan-ignore staticProperty.notFound
        if (! is_string($modelClass) || ! class_exists($modelClass)) {
            return false;
        }

        $policy = Gate::getPolicyFor(new $modelClass());

        return is_object($policy) && method_exists($policy, $policyMethod);
    }
}
