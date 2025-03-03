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

if (! Auth::user()->hasGlobalAdmin()) {
    $status = ['status' => 1, 'message' => 'You need to be admin'];
} else {
    if ($_POST['device_id']) {
        if (! is_numeric($_POST['device_id'])) {
            $status = ['status' => 1, 'message' => 'Wrong device id!'];
        } else {
            $device = \App\Models\Device::find($_POST['device_id']);
            if ($device->parents()->detach()) {
                $status = ['status' => 0, 'message' => 'Device dependency has been deleted.'];
            } else {
                $status = ['status' => 1, 'message' => 'Device dependency cannot be deleted.'];
            }
        }
    } elseif ($_POST['parent_ids']) {
        $status = ['status' => 0, 'message' => 'Device dependencies has been deleted'];
        foreach ($_POST['parent_ids'] as $parent) {
            if (is_numeric($parent) && $parent != 0) {
                $device = \App\Models\Device::find($_POST['device_id']);
                if (! $device->children()->detach()) {
                    $status = ['status' => 1, 'message' => 'Device dependency cannot be deleted.'];
                }
            } elseif ($parent == 0) {
                $status = ['status' => 1, 'message' => 'No dependency to delete.'];
                break;
            }
        }
    }
}

header('Content-Type: application/json');
echo json_encode($status);
