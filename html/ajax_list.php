<?php

/*
 * LibreNMS
 *
 * Copyright (c) 2014 Neil Lathwood <https://github.com/laf/ http://www.lathwood.co.uk/fa>
 * Copyright (c) 2017 Tony Murray <https://github.com/murrant/>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

use LibreNMS\Util\Debug;

$init_modules = ['web', 'auth'];
require realpath(__DIR__ . '/..') . '/includes/init.php';

if (! Auth::check()) {
    exit('Unauthorized');
}

Debug::set($_REQUEST['debug']);

$type = basename($_REQUEST['type']);

if ($type && file_exists("includes/html/list/$type.inc.php")) {
    header('Content-type: application/json');

    [$results, $more] = include "includes/html/list/$type.inc.php";

    exit(json_encode([
        'results' => $results,
        'pagination' => ['more' => $more],
    ]));
}
