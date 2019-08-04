<?php

/*
 * LibreNMS
 *
 * Copyright (c) 2015 Neil Lathwood <https://github.com/laf/ http://www.lathwood.co.uk/fa>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

header('Content-type: application/json');

if (!Auth::user()->hasGlobalAdmin()) {
    $response = array(
        'status'  => 'error',
        'message' => 'Need to be admin',
    );
    echo _json_encode($response);
    exit;
}

$status  = 'error';
$message = 'Error updating storage information';

$device_id = mres($_POST['device_id']);
$storage_id = mres($_POST['storage_id']);
$data = mres($_POST['data']);

if (!is_numeric($device_id)) {
    $message = 'Missing device id';
} elseif (!is_numeric($storage_id)) {
    $message = 'Missing storage id';
} elseif (!is_numeric($data)) {
    $message = 'Missing value';
} else {
    if (dbUpdate(array('storage_perc_warn'=>$data), 'storage', '`storage_id`=? AND `device_id`=?', array($storage_id,$device_id)) >= 0) {
        $message = 'Storage information updated';
        $status = 'ok';
    } else {
        $message = 'Could not update storage information';
    }
}

$response = array(
    'status'        => $status,
    'message'       => $message,
    'extra'         => $extra,
);
echo _json_encode($response);
