<?php

/*
 * LibreNMS
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

header('Content-type: application/json');

if (! Auth::user()->hasGlobalAdmin()) {
    exit(json_encode([
        'status' => 'error',
        'message' => 'You need to be admin',
    ]));
}

$transport_id = $vars['transport_id'];
// Retrieve alert transport
if (is_numeric($transport_id) && $transport_id > 0) {
    $transport = dbFetchRow('SELECT * FROM `alert_transports` WHERE `transport_id` =? LIMIT 1', [$transport_id]);

    if ($transport['is_default'] == true) {
        $is_default = true;
    } else {
        $is_default = false;
    }
    $details = [];
    // Get alert transport configuration details
    foreach (json_decode($transport['transport_config'], true) as $key => $value) {
        $details[] = [
            'name' => $key,
            'value' => $value,
        ];
    }
}

if (is_array($transport)) {
    exit(json_encode([
        'name' => $transport['transport_name'],
        'type' => $transport['transport_type'],
        'is_default' => $is_default,
        'details' => $details,
    ]));
} else {
    exit(json_encode([
        'status' => 'error',
        'message' => 'No alert transport found',
    ]));
}
