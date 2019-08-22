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

$init_modules = array('web', 'auth');
require realpath(__DIR__ . '/..') . '/includes/init.php';

if (!Auth::check()) {
    die('Unauthorized');
}

set_debug($_REQUEST['debug']);

$current = $_REQUEST['current'];
settype($current, 'integer');
$rowCount = $_REQUEST['rowCount'];
settype($rowCount, 'integer');
if (isset($_REQUEST['sort']) && is_array($_POST['sort'])) {
    foreach ($_REQUEST['sort'] as $k => $v) {
        $sort .= " $k $v";
    }
}

$searchPhrase = $_REQUEST['searchPhrase'];
$id           = basename($_REQUEST['id']);
$response     = array();

if ($id && file_exists("includes/html/table/$id.inc.php")) {
    header('Content-type: application/json');
    include_once "includes/html/table/$id.inc.php";
}
