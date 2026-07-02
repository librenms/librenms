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
        $filter = $request->array('filter');

        // Explicit clear via '?filter=' parameter
        if ($request->has('filter') && empty($filter)) {
            $request->merge(['filter' => []]);

            return $next($request);
        }

        if (auth()->check()) {
            $prefKey = 'filters.' . $filterName;
            $savedFilter = UserPref::getPref($request->user(), $prefKey) ?: [];
            $request->merge(['filter' => array_merge($savedFilter, $filter)]);
        }

        return $next($request);
    }
}
