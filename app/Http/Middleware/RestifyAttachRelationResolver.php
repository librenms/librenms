<?php

namespace App\Http\Middleware;

use Binaryk\LaravelRestify\Fields\EagerField;
use Binaryk\LaravelRestify\Restify;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Lets repositories advertise URL-friendly attach/sync/detach segments that
 * don't match the underlying Eloquent relation method name.
 *
 * For URLs like /api/v1/{parent}/{id}/(attach|sync|detach)/{segment}, we look
 * up the field on the parent repository whose `attribute` equals `{segment}`
 * and, when its `relation` property differs (e.g. attribute='device-groups',
 * relation='groups'), inject `viaRelationship` on the request so Restify's
 * controllers call `$model->groups()` rather than the literal URL segment.
 *
 * Effectively decouples the URL slug from the model method, which PHP requires
 * to be a valid identifier (no hyphens).
 */
class RestifyAttachRelationResolver
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! preg_match('#^api/v1/([^/]+)/[^/]+/(?:attach|sync|detach)/([^/?]+)#', $request->path(), $m)) {
            return $next($request);
        }

        [$_, $parentSegment, $relatedSegment] = $m;

        $repoClass = Restify::repositoryClassForKey($parentSegment);
        if (! $repoClass || ! method_exists($repoClass, 'related')) {
            return $next($request);
        }

        foreach ($repoClass::related() as $field) {
            if (! $field instanceof EagerField || $field->attribute !== $relatedSegment) {
                continue;
            }

            if (isset($field->relation) && $field->relation !== $field->attribute) {
                // Laravel Request's __get checks input first then route params;
                // merging the value into the input bag is the simplest way to
                // make vendor's `$request->viaRelationship` resolve to the
                // model method name we want (e.g. groups()).
                $request->merge(['viaRelationship' => $field->relation]);
            }
            break;
        }

        return $next($request);
    }
}
