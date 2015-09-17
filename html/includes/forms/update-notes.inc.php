<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2015 Søren Friis Rosiak <sorenrosiak@gmail.com>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$status    = 'error';
$message   = 'unknown error';

$device_id = mres($_POST['device_id']);
$notes = mres($_POST['notes']);

if (isset($notes) && (dbUpdate(array('notes' => $notes), 'devices', 'device_id = ?', array($device_id)))) {
    $status  = 'ok';
    $message = 'Updated';
}
else {
    $status  = 'error';
    $message = 'ERROR: Could not update';
}
die(json_encode(array(
    'status'       => $status,
    'message'      => $message,
    'notes'        => $notes,
    'device_id'    => $device_id
)));
