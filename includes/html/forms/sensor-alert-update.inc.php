<?php

/*
 * LibreNMS
 *
 * Copyright (c) 2014 Neil Lathwood <https://github.com/laf/ http://www.lathwood.co.uk>
 * Copyright (c) 2018 TheGreatDoc <https://github.com/TheGreatDoc>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

header('Content-type: application/json');

if (! Auth::user()->hasGlobalAdmin()) {
    $response = [
        'status'  => 'error',
        'message' => 'Need to be admin',
    ];
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

if (isset($_POST['sub_type']) && ! empty($_POST['sub_type'])) {
    $status = 'error';
    $message = 'Error removing custom';
    if (dbUpdate(['sensor_custom' => 'No'], 'sensors', '`sensor_id` = ?', [$_POST['sensor_id']]) >= 0) {
        $status = 'ok';
        $message = 'Custom limit removed. New one will be set up in rediscovery';
    } else {
        $message = 'Couldn\'t not remove custom. Enable debug and check logfile';
    }
} else {
    if (! is_numeric($_POST['device_id']) || ! is_numeric($_POST['sensor_id'])) {
        $message = 'Invalid device or sensor id';
    } else {
        if ($_POST['state'] == 'true') {
            $state = 1;
            $state_string = 'enabled';
        } elseif ($_POST['state'] == 'false') {
            $state = 0;
            $state_string = 'disabled';
        } else {
            $state = 0;
            $state_string = 'disabled';
        }
        if (dbUpdate(['sensor_alert' => $state], 'sensors', '`sensor_id` = ? AND `device_id` = ?', [$_POST['sensor_id'], $_POST['device_id']]) >= 0) {
            $status = ($state == 0) ? 'info' : 'ok';
            $message = 'Alerts ' . $state_string . ' for sensor ' . $_POST['sensor_desc'];
        } else {
            $status = 'error';
            $message = 'Couldn\'t ' . substr($state_string, 0, -1) . ' alerts for sensor ' . $_POST['sensor_desc'] . '. Enable debug and check librenms.log';
        }
    }
}
$response = [
    'status'        => $status,
    'message'       => $message,
];
echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
