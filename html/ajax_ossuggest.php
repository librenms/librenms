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

use LibreNMS\Util\Debug;

$init_modules = ['web', 'auth'];
require realpath(__DIR__ . '/..') . '/includes/init.php';

if (! Auth::check()) {
    exit('Unauthorized');
}

Debug::set($_REQUEST['debug']);

/**
 * Levenshtein Sort
 * @param string $base Comparison basis
 * @param array  $obj  Object to sort
 * @return array
 */
function levsortos($base, $obj, $keys)
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

header('Content-type: application/json');
if (isset($_GET['term'])) {
    \LibreNMS\Util\OS::loadAllDefinitions(false, true);
    $_GET['term'] = strip_tags($_GET['term']);
    $sortos = levsortos($_GET['term'], \LibreNMS\Config::get('os'), ['text', 'os']);
    $sortos = array_slice($sortos, 0, 20);
    foreach ($sortos as $lev => $os) {
        $ret[$lev] = array_intersect_key($os, ['os' => true, 'text' => true]);
    }
}
if (! isset($ret)) {
    $ret = [['Error: No suggestions found.']];
}

exit(json_encode($ret));
