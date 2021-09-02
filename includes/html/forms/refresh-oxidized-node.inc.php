<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2018 PipoCanaja <pipocanaja@gmail.com>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

header('Content-type: application/json');

$device_hostname = strip_tags($_POST['device_hostname']);
if (Auth::user()->hasGlobalAdmin() && isset($device_hostname)) {
    if (oxidized_node_update($device_hostname, 'LibreNMS GUI refresh', Auth::user()->username)) {
        $status = 'ok';
        $message = 'Queued refresh in oxidized for device ' . $device_hostname;
    } else {
        $status = 'error';
        $message = 'ERROR: Could not queue refresh of oxidized device' . $device_hostname;
    }
} else {
    $status = 'error';
    $message = 'ERROR: Could not queue refresh oxidized device';
}

$output = [
    'status'  => $status,
    'message' => $message,
];

header('Content-type: application/json');
echo json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
