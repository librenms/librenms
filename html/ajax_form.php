<?php

/*
 * LibreNMS
 *
 * Copyright (c) 2014 Neil Lathwood <https://github.com/laf/ http://www.lathwood.co.uk>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

// FUA

require_once '../includes/defaults.inc.php';
require_once '../config.php';
require_once '../includes/definitions.inc.php';
require_once 'includes/functions.inc.php';
require_once '../includes/functions.php';
require_once 'includes/authenticate.inc.php';
require_once 'includes/vars.inc.php';
require_once '../includes/alerts.inc.php';

set_debug($_REQUEST['debug']);

if (!$_SESSION['authenticated']) {
    echo 'unauthenticated';
    exit;
}

if (preg_match('/^[a-zA-Z0-9\-]+$/', $_POST['type']) == 1) {
    if (file_exists('includes/forms/'.$_POST['type'].'.inc.php')) {
        include_once 'includes/forms/'.$_POST['type'].'.inc.php';
    }
}
