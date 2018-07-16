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

use LibreNMS\Authentication\Auth;

if (!Auth::user()->hasGlobalAdmin()) {
    $status = ['status' => 1, 'message' => 'You need to be admin'];
} else {
    foreach ($vars['parent_ids'] as $parent) {
        if (!is_numeric($parent)) {
            $status = ['status' => 1, 'message' => 'Parent ID must be an integer!'];
            break;
        }
    }

    if (count($vars['parent_ids']) > 1 && in_array('0', $vars['parent_ids'])) {
        $status = ['status' => 1, 'message' => 'Multiple parents cannot contain None-Parent!'];
    }

    // A bit of an effort to reuse this code with dependency editing and the dependency wizard (editing multiple hosts at the same time)
    $device_arr = [];
    foreach ($vars['device_ids'] as $dev) {
        if (!is_numeric($dev)) {
            $status = ['status' => 1, 'message' => 'Device ID must be an integer!'];
            break;
        } elseif (in_array($dev, $vars['parent_ids'])) {
            $status = ['status' => 1, 'message' => 'A device cannot depend itself'];
            break;
        }
        $insert = [];
        foreach ($vars['parent_ids'] as $parent) {
            if (!is_numeric($parent)) {
                $parent = getidbyname($parent);
            }
            if (is_numeric($parent) && $parent != 0) {
                $insert[] = ['parent_device_id' => $parent, 'child_device_id' => $dev];
            } elseif ($parent == 0) {
                // In case we receive a mixed array with $parent = 0 (which shouldn't happen)
                // Empty the insert array so we remove any previous dependency so 'None' takes precedence
                $insert = [];
                break;
            }
        }
        dbDelete('device_relationships', '`child_device_id` = ?', [$dev]);
        if (!empty($insert)) {
            dbBulkInsert($insert, 'device_relationships');
        }
        $status = ['status' => 0, 'message' => 'Device dependencies have been saved'];
    }
}
header('Content-Type: application/json');
echo _json_encode($status);
