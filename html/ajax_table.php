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

use LibreNMS\Authentication\LegacyAuth;

$init_modules = array('web', 'auth');
require realpath(__DIR__ . '/..') . '/includes/init.php';

if (!LegacyAuth::check()) {
    echo "Unauthenticated\n";
    exit;
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

$searchPhrase = mres($_REQUEST['searchPhrase']);
$id           = mres($_REQUEST['id']);
$response     = array();

if (isset($id)) {
    if (file_exists("includes/table/$id.inc.php")) {
        header('Content-type: application/json');
        include_once "includes/table/$id.inc.php";
    }
}
