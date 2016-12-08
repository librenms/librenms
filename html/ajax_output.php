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

$init_modules = array('web', 'auth', 'alerts');
require realpath(__DIR__ . '/..') . '/includes/init.php';

if (!$_SESSION['authenticated']) {
    echo "Unauthenticated\n";
    exit;
}

set_debug($_REQUEST['debug']);
$id = mres($_REQUEST['id']);

if (isset($id)) {
    require $config['install_dir'] . "/html/includes/output/$id.inc.php";
}
