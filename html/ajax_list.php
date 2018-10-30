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

use LibreNMS\Authentication\LegacyAuth;

$init_modules = array('web', 'auth');
require realpath(__DIR__ . '/..') . '/includes/init.php';

if (!LegacyAuth::check()) {
    echo "Unauthenticated\n";
    exit;
}

set_debug($_REQUEST['debug']);

$type = mres($_REQUEST['type']);

if (isset($type) && file_exists("includes/list/$type.inc.php")) {
    header('Content-type: application/json');

    list($results, $more) = include "includes/list/$type.inc.php";

    die(json_encode([
        'results' => $results,
        'pagination' => ['more' => $more]
    ]));
}
