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

$status = 'error';
$message = 'Error resetting values';
$sensor_limit = $_POST['sensor_limit'];
$sensor_limit_warn = $_POST['sensor_limit_warn'];
$sensor_limit_low = $_POST['sensor_limit_low'];
$sensor_limit_low_warn = $_POST['sensor_limit_low_warn'];
$sensor_alert = $_POST['sensor_alert'];
$sensor_id = $_POST['sensor_id'];
$sensor_count = count($sensor_id);

if (is_array($sensor_id)) {
    for ($x = 0; $x < $sensor_count; $x++) {
        if (dbUpdate(['sensor_limit' => set_null($sensor_limit[$x], ['NULL']), 'sensor_limit_warn' => set_null($sensor_limit_warn[$x], ['NULL']), 'sensor_limit_low_warn' => set_null($sensor_limit_low_warn[$x], ['NULL']), 'sensor_limit_low' => set_null($sensor_limit_low[$x], ['NULL'])], 'sensors', '`sensor_id` = ?', [$sensor_id[$x]]) >= 0) {
            $message = 'Sensor values resetted';
            $status = 'ok';
        } else {
            $message = 'Could not reset sensors values';
        }
    }
} else {
    $status = 'error';
    $message = 'Invalid sensor id';
}
$response = [
    'status'        => $status,
    'message'       => $message,
];
echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
