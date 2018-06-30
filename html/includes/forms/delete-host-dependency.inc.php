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
    $status = array('status' => 1, 'message' => 'You need to be admin');
} else {
    if ($_POST['device_id']) {
        if (!is_numeric($_POST['device_id'])) {
            $status = array('status' => 1, 'message' => 'Wrong device id!');
        } else {
            if (dbDelete('device_relationships', '`child_device_id` = ?', array($_POST['device_id']))) {
                $status = array('status' => 0, 'message' => 'Device dependency has been deleted.');
            } else {
                $status = array('status' => 1, 'message' => 'Device dependency cannot be deleted.');
            }
        }
    } elseif ($_POST['parent_ids']) {
        $error = false;
        foreach ($_POST['parent_ids'] as $parent) {
            if (is_numeric($parent) && $parent != 0) {
                if (!dbDelete('device_relationships', ' `parent_device_id` = ?', array($parent))) {
                    $error = true;
                    $status = array('status' => 1, 'message' => 'Device dependency cannot be deleted.');
                }
            } elseif ($parent == 0) {
                $status = array('status' => 1, 'message' => 'No dependency to delete.');
                $error = true;
                break;
            }
        }

        if (!$error) {
            $status = array('status' => 0, 'message' => 'Device dependencies has been deleted');
        } else {
        }
    }
}

header('Content-Type: application/json');
echo _json_encode($status);
