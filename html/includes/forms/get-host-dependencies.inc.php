<?php

/*
 * LibreNMS
 *
 * Copyright (c) 2017 Aldemir Akpinar <https://github.com/aldemira/>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */
header('Content-type: application/json');

if (is_admin() === false) {
    die('ERROR: You need to be admin');
}

syslog(LOG_INFO, 'alo');
if (isset($_POST['viewtype'])) {
    if ($_POST['viewtype'] == 'fulllist') {
        $device_deps = dbFetchRows("SELECT a.device_id as id, a.hostname as child, b.hostname as parent, b.device_id as parentid from devices as a LEFT JOIN devices as b ON a.parent_id = b.device_id ORDER BY a.hostname");
        echo json_encode($device_deps);
        exit;
    } else if ($_POST['viewtype'] == 'fromparent' && is_numeric($_POST['parent_id'])) {
        // Another awful hack
        if ($_POST['parent_id'] == 0) {
            $device_deps = dbFetchRows('SELECT `device_id`,`hostname` from `devices` WHERE `parent_id` = 0 OR `parent_id` is null ORDER BY `hostname` ASC');
        } else {
            $device_deps = dbFetchRows('SELECT `device_id`,`hostname` from `devices` WHERE `parent_id` = ? ORDER BY `hostname` ASC', array($_POST['parent_id']));
        }
        echo json_encode($device_deps);
        exit;
    }
}

if (!is_numeric($_POST['device_id'])) {
    echo 'ERROR: Wrong device id!';
    exit;
} else {
    $device_deps = dbFetchRows('SELECT `device_id`,`hostname`,`parent_id` from `devices` WHERE `device_id` <>  ? ORDER BY `hostname` ASC', array($_POST['device_id']));
    echo json_encode($device_deps);
    exit;
}
