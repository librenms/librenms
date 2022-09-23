<?php

/*
 * LibreNMS
 *
 * Copyright (c) 2014 Neil Lathwood <https://github.com/laf/ http://www.lathwood.co.uk>
 * Copyright (c) 2017 Neil Lathwood <https://github.com/murrant>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

header('Content-type: application/json');

// FUA

if (! Auth::user()->hasGlobalAdmin()) {
    exit(json_encode([
        'status' => 'error',
        'message' => 'You need to be admin',
    ]));
}

if (! is_numeric($_POST['device_id']) || ! is_numeric($_POST['sensor_id']) || ! isset($_POST['data'])) {
    exit(json_encode([
        'status' => 'error',
        'message' => 'Invalid values given',
    ]));
} else {
    $update = dbUpdate(
        [$_POST['value_type'] => set_null($_POST['data'], ['NULL']), 'sensor_custom' => 'Yes'],
        'wireless_sensors',
        '`sensor_id` = ? AND `device_id` = ?',
        [$_POST['sensor_id'], $_POST['device_id']]
    );
    if (! empty($update) || $update == '0') {
        exit(json_encode([
            'status' => 'ok',
            'message' => 'Updated sensor value',
        ]));
    } else {
        exit(json_encode([
            'status' => 'error',
            'message' => 'Failed to update sensor value',
        ]));
    }
}
