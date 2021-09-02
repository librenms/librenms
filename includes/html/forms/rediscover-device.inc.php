<?php

/*
 * LibreNMS
 *
 * Copyright (c) 2014 Neil Lathwood <https://github.com/laf/ http://www.lathwood.co.uk/fa>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

if (! Auth::user()->hasGlobalAdmin()) {
    $response = [
        'status'  => 'error',
        'message' => 'Need to be admin',
    ];
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

if (isset($_POST['device_id'])) {
    if (! is_numeric($_POST['device_id'])) {
        $status = 'error';
        $message = 'Invalid device id ' . $_POST['device_id'];
    } else {
        $result = device_discovery_trigger($_POST['device_id']);
        if (! empty($result['status']) || $result['status'] == '0') {
            $status = 'ok';
        } else {
            $status = 'error';
        }
        $message = $result['message'];
    }
} elseif (isset($_POST['device_group_id'])) {
    if (! is_numeric($_POST['device_group_id'])) {
        $status = 'error';
        $message = 'Invalid device group id ' . $_POST['device_group_id'];
    } else {
        $device_ids = dbFetchColumn('SELECT `device_id` FROM `device_group_device` WHERE `device_group_id` = ?', [$_POST['device_group_id']]);
        $update = 0;
        foreach ($device_ids as $device_id) {
            $result = device_discovery_trigger($device_id);
            $update += $result['status'];
        }

        if (! empty($update) || $update == '0') {
            $status = 'ok';
            $message = 'Devices of group ' . $_POST['device_group_id'] . ' will be rediscovered';
        } else {
            $status = 'error';
            $message = 'Error rediscovering devices of group ' . $_POST['device_group_id'];
        }
    }
} else {
    $status = 'Error';
    $message = 'Undefined POST keys received';
}

$output = [
    'status'  => $status,
    'message' => $message,
];

header('Content-type: application/json');
echo json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
