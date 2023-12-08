<?php

namespace App\Http\Controllers\Ajax;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use \LibreNMS\Config;
class OsSearchController extends Controller
{
    /**
     * Levenshtein Sort
     *
     * @param  string  $base  Comparison basis
     * @param  array  $obj  Object to sort
     * @return array
     */
    protected function levsortos($base, $obj, $keys)
    {
        $ret = [];
        foreach ($obj as $elem) {
            $lev = false;
            foreach ($keys as $key) {
                $levnew = levenshtein(strtolower($base), strtolower($elem[$key]), 1, 10, 10);
                if ($lev === false || $levnew < $lev) {
                    $lev = $levnew;
                }
            }
            while (isset($ret["$lev"])) {
                $lev += 0.1;
            }

            $ret["$lev"] = $elem;
        }

        ksort($ret);

        return $ret;
    }

    public function handleRequest(Request $request)
    {
        if (! Auth::check()) {
            abort(403, 'Unauthorized');
        }

        if ($request->has('term')) {
            // It's not clear how to implement os definitions for Laravel so for this code, I'm assuring "os" as a config key with value as an array.
            $osDefinitions = Config('os');
            $term = strip_tags($request->input('term'));
            $sortos = $this->levsortos($term, $osDefinitions, ['text', 'os']);
            $sortos = array_slice($sortos, 0, 20);
            foreach ($sortos as $lev => $os) {
                $ret[$lev] = array_intersect_key($os, ['os' => true, 'text' => true]);
            }
        }
        if (! isset($ret)) {
            $ret = ['Error: No suggestions found.'];
        }

        return response()->json($ret);
    }
}
