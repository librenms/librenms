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
        foreach ($_POST['parent_ids'] as $parent) {
            if (is_numeric($parent) && $parent != 0) {
                dbInsert(array('parent_device_id' => $parent, 'child_device_id' => $dev), 'device_relationships');
            } else if ($parent == 0) {
                // If we receive the parent as 0 delete parents for the said device
                dbDelete('device_relationships', '`child_device_id` = ?', array($dev));
            }
        }
        $status = array('status' => 0, 'message' => 'Host dependencies have been saved');
    }
}
header('Content-Type: application/json');
echo _json_encode($status);
