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
header('Content-type: text/plain');

if (is_admin() === false) {
    die('ERROR: You need to be admin');
}

if (!is_numeric($_POST['alert_id'])) {
    echo 'ERROR: No alert selected';
    exit;
} else {
    if (dbDelete('alert_rules', '`id` =  ?', array($_POST['alert_id']))) {
        if (dbDelete('alert_map', 'rule = ?', array($_POST['alert_id'])) || dbFetchCell('SELECT COUNT(*) FROM alert_map WHERE rule = ?', array($_POST['alert_id'])) == 0) {
            echo 'Maps has been deleted.';
        } else {
            echo 'WARNING: Maps could not be deleted.';
        }

        echo 'Alert rule has been deleted.';
        exit;
    } else {
        echo 'ERROR: Alert rule has not been deleted.';
        exit;
    }
}
