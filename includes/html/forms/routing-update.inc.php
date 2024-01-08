<?php

/*
 * LibreNMS
 *
 * Copyright (c) 2018 TheGreatDoc
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
$message = 'Error updating routing information';

$device_id = $_POST['device_id'];
$routing_id = $_POST['routing_id'];
$data = $_POST['data'];

if (! is_numeric($device_id)) {
    $message = 'Missing device id';
} elseif (! is_numeric($routing_id)) {
    $message = 'Missing routing id';
} else {
    if (dbUpdate(['bgpPeerDescr'=>$data], 'bgpPeers', '`bgpPeer_id`=? AND `device_id`=?', [$routing_id, $device_id]) >= 0) {
        $message = 'Routing information updated';
        $status = 'ok';
    } else {
        $message = 'Could not update Routing information';
    }
}

$response = [
    'status'        => $status,
    'message'       => $message,
    'extra'         => $extra,
];
echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
