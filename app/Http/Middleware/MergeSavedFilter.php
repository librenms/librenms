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
     */
    public function handle(Request $request, Closure $next, string $filterName): Response
    {
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
