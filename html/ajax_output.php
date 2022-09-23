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

session_start();
session_write_close();
if (isset($_SESSION['stage']) && $_SESSION['stage'] == 2) {
    $init_modules = ['web', 'nodb'];
    require realpath(__DIR__ . '/..') . '/includes/init.php';
} else {
    $init_modules = ['web', 'auth', 'alerts'];
    require realpath(__DIR__ . '/..') . '/includes/init.php';

    if (! Auth::check()) {
        exit('Unauthorized');
    }
}

Debug::set($_REQUEST['debug']);
$id = basename($_REQUEST['id']);

if ($id && is_file(\LibreNMS\Config::get('install_dir') . "/includes/html/output/$id.inc.php")) {
    require \LibreNMS\Config::get('install_dir') . "/includes/html/output/$id.inc.php";
}
