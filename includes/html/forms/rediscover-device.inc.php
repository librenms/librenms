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

if (!Auth::user()->hasGlobalAdmin()) {
    $response = array(
        'status'  => 'error',
        'message' => 'Need to be admin',
    );
    echo _json_encode($response);
    exit;
}

// FIXME: Make this part of the API instead of a standalone function
if (!is_numeric($_POST['device_id'])) {
    $status  = 'error';
    $message = 'Invalid device id';
} else {
    $update = dbUpdate(array('last_discovered' => array('NULL')), 'devices', '`device_id` = ?', array($_POST['device_id']));
    if (!empty($update) || $update == '0') {
        $status  = 'ok';
        $message = 'Device will be rediscovered';
    } else {
         $status  = 'error';
         $message = 'Error rediscovering device';
    }
}

$output = array(
    'status'  => $status,
    'message' => $message,
);

header('Content-type: application/json');
echo _json_encode($output);
