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

use App\Models\AlertTransport;
use Illuminate\Support\Facades\Gate;

header('Content-type: application/json');

$transport_id = $vars['transport_id'];
$transport = AlertTransport::findOrFail($transport_id);
if (Gate::denies('view', $transport)) {
    exit(json_encode([
        'status' => 'error',
        'message' => 'You need permission',
    ]));
}

// Retrieve alert transport
if (is_numeric($transport_id) && $transport_id > 0) {
    $details = [];
    // Get alert transport configuration details
    foreach ($transport->transport_config as $key => $value) {
        $details[] = [
            'name' => $key,
            'value' => $value,
        ];
    }
}

if ($transport->exists) {
    exit(json_encode([
        'name' => $transport->transport_name,
        'type' => $transport->transport_type,
        'is_default' => $transport->is_default,
        'details' => $details,
    ]));
} else {
    // not reachable
    exit(json_encode([
        'status' => 'error',
        'message' => 'No alert transport found',
    ]));
}
