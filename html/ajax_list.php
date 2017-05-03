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

$init_modules = array('web', 'auth');
require realpath(__DIR__ . '/..') . '/includes/init.php';

if (!$_SESSION['authenticated']) {
    echo "Unauthenticated\n";
    exit;
}

set_debug($_REQUEST['debug']);

$id = mres($_REQUEST['id']);

if (isset($id)) {
    if (file_exists("includes/list/$id.inc.php")) {
        header('Content-type: application/json');
        include_once "includes/list/$id.inc.php";
    }
}
