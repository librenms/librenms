<?php

namespace App\Http\Middleware;

use Binaryk\LaravelRestify\Restify;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Returns a JSON:API 404 for relationship routes whose parent record does not exist.
 *
 * Restify's relatable routes (/api/v1/{parentRepository}/{parentRepositoryId}/{repository})
 * load the parent with `whereKey($parentRepositoryId)->first()` and then resolve the
 * relation off that model. When the parent id matches no row the model is null and the
 * relation call (`null->relation()`) throws, surfacing as a 500. The OpenAPI document
 * advertises 404 for these routes, so we detect the missing parent up front and return it.
 */
class EnsureRelatedParentExists
{
    public function handle(Request $request, Closure $next): Response
    {
        $route = $request->route();
        $parentKey = $route?->parameter('parentRepository');
        $parentId = $route?->parameter('parentRepositoryId');

        if (is_string($parentKey) && $parentId !== null) {
            $repoClass = Restify::repositoryClassForKey($parentKey);

            if ($repoClass && ! $repoClass::newModel()->newQuery()->whereKey($parentId)->exists()) {
                return response()->json([
                    'errors' => [[
                        'status' => '404',
                        'code' => 'not_found',
                        'title' => 'Not Found',
                        'detail' => "No {$parentKey} resource matches the id [{$parentId}].",
                    ]],
                ], 404);
            }
        }

        return $next($request);
    }
}
