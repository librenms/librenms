<?php

namespace App\Http\Middleware;

use App\Models\UserPref;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MergeSavedFilter
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(Request): (Response)  $next
     * @param  string  $filterName  The domain name of the filter (e.g., 'device.ports')
     * @param  string|null  $paths  Optional pipe-separated list of Request::is() glob patterns.
     *                              If given, the filter only applies when the path matches one of them.
     */
    public function handle(Request $request, Closure $next, string $filterName, ?string $paths = null): Response
    {
        if ($paths !== null && ! $request->is(...explode('|', $paths))) {
            return $next($request);
        }

        // Filters in the url replace the saved filter, '?filter=' explicitly clears it
        if ($request->has('filter')) {
            $request->merge(['filter' => $request->array('filter')]);

            return $next($request);
        }

        if ($request->user() !== null) {
            $prefKey = 'filters.' . $filterName;
            $request->merge(['filter' => UserPref::getPref($request->user(), $prefKey) ?: []]);
        }

        return $next($request);
    }
}
