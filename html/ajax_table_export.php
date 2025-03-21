<?php

/*
 * LibreNMS
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

Debug::set(! empty($_REQUEST['debug']));

$_REQUEST['export'] = true;

$current = $_REQUEST['current'];
settype($current, 'integer');
$rowCount = -1;

$id = basename($_REQUEST['id']);

if ($id && file_exists("includes/html/table/$id.inc.php")) {
    // Set proper headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $id . '-' . date('Y-m-d') . '.csv');

    $output = fopen('php://output', 'w');

    include_once "includes/html/table/$id.inc.php";
} else {
    http_response_code(404);
    echo "Table not found";
}
