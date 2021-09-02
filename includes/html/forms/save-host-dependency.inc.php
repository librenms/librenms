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
    $parent_ids = (array) $_POST['parent_ids'];
    $device_ids = (array) $_POST['device_ids'];

    foreach ($parent_ids as $parent) {
        if (! is_numeric($parent)) {
            $status = ['status' => 1, 'message' => 'Parent ID must be an integer!'];
            break;
        }
    }

    if (count($parent_ids) > 1 && in_array('0', $parent_ids)) {
        $status = ['status' => 1, 'message' => 'Multiple parents cannot contain None-Parent!'];
    }

    foreach ($device_ids as $device_id) {
        if (! is_numeric($device_id)) {
            $status = ['status' => 1, 'message' => 'Device ID must be an integer!'];
            break;
        } elseif (in_array($device_id, $parent_ids)) {
            $status = ['status' => 1, 'message' => 'A device cannot depend itself'];
            break;
        }

        \App\Models\Device::find($device_id)->parents()->sync($parent_ids);

        $status = ['status' => 0, 'message' => 'Device dependencies have been saved'];
    }
}
header('Content-Type: application/json');
echo json_encode($status);
