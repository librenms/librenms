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
    if ($vars['device_id']) {
        if (!is_numeric($vars['device_id'])) {
            $status = ['status' => 1, 'message' => 'Wrong device id!'];
        } else {
            if (dbDelete('device_relationships', '`child_device_id` = ?', [$vars['device_id']])) {
                $status = ['status' => 0, 'message' => 'Device dependency has been deleted.'];
            } else {
                $status = ['status' => 1, 'message' => 'Device dependency cannot be deleted.'];
            }
        }
    } elseif ($vars['parent_ids']) {
        $error = false;
        foreach ($vars['parent_ids'] as $parent) {
            if (!is_numeric($parent)) {
                $parent = getidbyname($parent);
            }
            if (is_numeric($parent) && $parent != 0) {
                if (!dbDelete('device_relationships', ' `parent_device_id` = ?', [$parent])) {
                    $error = true;
                    $status = ['status' => 1, 'message' => 'Device dependency cannot be deleted.'];
                }
            } elseif ($parent == 0) {
                $status = ['status' => 1, 'message' => 'No dependency to delete.'];
                $error = true;
                break;
            }
        }

        if (!$error) {
            $status = ['status' => 0, 'message' => 'Device dependencies has been deleted'];
        } else {
        }
    }
}

header('Content-Type: application/json');
echo _json_encode($status);
