<?php

/*
 * LibreNMS
 *
 * Copyright (c) 2014 Neil Lathwood <https://github.com/laf/ http://www.lathwood.co.uk/fa>
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

$current = $_REQUEST['current'];
settype($current, 'integer');
$rowCount = $_REQUEST['rowCount'];
settype($rowCount, 'integer');
if (isset($_REQUEST['sort']) && is_array($_REQUEST['sort'])) {
    foreach ($_REQUEST['sort'] as $k => $v) {
        $k = preg_replace('/[^A-Za-z0-9_]/', '', $k); // only allow plain columns
        $v = strtolower($v) == 'desc' ? 'DESC' : 'ASC';
        $sort .= " $k $v";
    }
}

$searchPhrase = $_REQUEST['searchPhrase'];
$id = basename($_REQUEST['id']);
$response = [];

if ($id && file_exists("includes/html/table/$id.inc.php")) {
    header('Content-type: application/json');
    include_once "includes/html/table/$id.inc.php";
}
