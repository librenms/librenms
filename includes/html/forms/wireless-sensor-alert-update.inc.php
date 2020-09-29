<?php

/*
 * LibreNMS
 *
 * Copyright (c) 2014 Neil Lathwood <https://github.com/laf/ http://www.lathwood.co.uk>
 * Copyright (c) 2017 Tony Murray <https://github.com/murrant>
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

if (isset($_POST['sub_type']) && $_POST['sub_type'] == 'remove-custom') {
    if (dbUpdate(['sensor_custom' => 'No'], 'wireless_sensors', '`sensor_id` = ?', [$_POST['sensor_id']])) {
        exit(json_encode([
            'status' => 'ok',
            'message' => 'Custom limit removed',
        ]));
    }

    exit(json_encode([
        'status' => 'error',
        'message' => 'Could not remove custom. Check librenms.log',
    ]));
} else {
    if (! is_numeric($_POST['device_id']) || ! is_numeric($_POST['sensor_id'])) {
        exit(json_encode([
            'status' => 'error',
            'message' => 'Invalid values given',
        ]));
    } else {
        if ($_POST['state'] == 'true') {
            $state = 1;
        } elseif ($_POST['state'] == 'false') {
            $state = 0;
        } else {
            $state = 0;
        }

        $update = dbUpdate(
            ['sensor_alert' => $state],
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
}
