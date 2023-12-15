<?php
/*
 * Copyright (C) 2017 Oscar Ekeroth <zmegolaz@gmail.com>
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Http\Controllers\Ajax;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            while (isset($ret[$lev])) {
                $lev += 0.1;
            }

            $ret[$lev] = $elem;
        }

        ksort($ret);

        return $ret;
    }

    public function handleRequest(Request $request)
    {
        if (! Auth::check()) {
            abort(403, 'Unauthorized');
        }
        if (! $request->has('term')) {
            abort(400);
        }
        \LibreNMS\Util\OS::loadAllDefinitions(false, true);

        $osDefinitions = \LibreNMS\Config::get('os');
        $term = strip_tags($request->input('term'));
        $sortos = $this->levsortos($term, $osDefinitions, ['text', 'os']);
        $sortos = array_slice($sortos, 0, 20);
        foreach ($sortos as $lev => $os) {
            $ret[$lev] = array_intersect_key($os, ['os' => true, 'text' => true]);
        }

        if (! isset($ret)) {
            $ret = ['Error: No suggestions found.'];
        }

        return response()->json($ret);
    }
}
