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

if (is_admin() === false) {
    $status = array('status' => 1, 'message' => 'You need to be admin');
} else {
    if (!is_numeric($_POST['parent_id'])) {
        $status = array('status' => 1, 'message' => 'Wrong Parent host ID!');
    }
    // A bit of an effort to reuse this code with dependency editing and the dependency wizard (editing multiple hosts at the same time)
    $device_arr = array();
    foreach ($_POST['device_ids'] as $dev) {
        if (!is_numeric($dev)) {
            $status = array('status' => 1, 'message' => 'Wrong device IDs!');
            break;
        } elseif ($dev == $_POST['parent_id']) {
            $status = array('status' => 1, 'message' => 'A device cannot depend itself');
            break;
        }
        $device_arr[] = $dev;
    }

    if (!$status) {
        $clause = dbGenPlaceholders(count($device_arr));
 
        if (dbQuery('UPDATE `devices` set parent_id = ' . $_POST['parent_id'] . ' WHERE `device_id` IN' . $clause, $device_arr)) {
            $status = array('status' => 0, 'message' => 'Host dependencies have been set');
        } else {
            $status = array('status' => 1, 'message' => 'Host dependencies cannot be set.');
        }
    }
}

header('Content-Type: application/json');
echo _json_encode($status);
