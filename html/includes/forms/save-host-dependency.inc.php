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
    foreach ($_POST['parent_ids'] as $parent) {
        if (!is_numeric($parent)) {
            $status = array('status' => 1, 'message' => 'Parent ID must be an integer!');
            break;
        }
    }

    if (count($_POST['parent_ids']) > 1 && in_array('0', $_POST['parent_ids'])) {
        $status = array('status' => 1, 'message' => 'Multiple parents cannot contain None-Parent!');
    }

    // A bit of an effort to reuse this code with dependency editing and the dependency wizard (editing multiple hosts at the same time)
    $device_arr = array();
    foreach ($_POST['device_ids'] as $dev) {
        if (!is_numeric($dev)) {
            $status = array('status' => 1, 'message' => 'Device ID must be an integer!');
            break;
        } elseif (in_array($dev, $_POST['parent_ids'])) {
            $status = array('status' => 1, 'message' => 'A device cannot depend itself');
            break;
        }
        $device_arr[] = $dev;
    }

    if (!isset($status) || empty($status)) {
        $devclause = dbGenPlaceholders(count($device_arr));
        $parent_arr = implode(',', $_POST['parent_ids']);
 
        if (dbQuery('UPDATE `devices` SET `parent_id` = ?  WHERE `device_id` IN' . $devclause, array($parent_arr, $device_arr))) {
            $status = array('status' => 0, 'message' => 'Host dependencies have been set');
        } else {
            $status = array('status' => 1, 'message' => 'Host dependencies cannot be set.');
        }
    }
}

header('Content-Type: application/json');
echo _json_encode($status);
