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

session_start();
if (isset($_SESSION['stage']) && $_SESSION['stage'] == 2) {
    $init_modules = array('web', 'nodb');
    require realpath(__DIR__ . '/..') . '/includes/init.php';
} else {
    $init_modules = array('web', 'auth', 'alerts');
    require realpath(__DIR__ . '/..') . '/includes/init.php';

    if (!Auth::check()) {
        die('Unauthorized');
    }
}

set_debug($_REQUEST['debug']);
$id = basename($_REQUEST['id']);

if ($id && is_file(\LibreNMS\Config::get('install_dir') . "/includes/html/output/$id.inc.php")) {
    require \LibreNMS\Config::get('install_dir') . "/includes/html/output/$id.inc.php";
}
