<?php

/*
 * LibreNMS
 *
 * Copyright (c) 2014 Neil Lathwood <https://github.com/laf/ http://www.lathwood.co.uk>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
*/

if (is_admin() === false) {
    $response = array(
        'status'  => 'error',
        'message' => 'Need to be admin',
    );
    echo _json_encode($response);
    exit;
}

$device['device_id'] = mres($_POST['device_id']);
$attrib = mres($_POST['attrib']);
$state = mres($_POST['state']);
$status = 'error';
$message = 'Error with config';

if (empty($device['device_id'])) {
    $message = 'No device passed';
}
else {
    if ($state == true) {
        set_dev_attrib($device, $attrib, $state);
    }
    else {
        del_dev_attrib($device, $attrib);
    }
    $status = 'ok';
    $message = 'Config has been updated';
}

$response = array(
    'status'        => $status,
    'message'       => $message,
);
echo _json_encode($response);
