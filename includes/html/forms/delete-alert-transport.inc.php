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
use App\Models\AlertTransportMap;
use App\Models\TransportGroupTransport;
use Illuminate\Support\Facades\Gate;

header('Content-type: application/json');

if (Gate::denies('delete', AlertTransport::class)) {
    exit(json_encode([
        'status' => 'error',
        'message' => 'You need permission.',
    ]));
}

$status = 'ok';
$message = '';

if (! is_numeric($vars['transport_id'])) {
    $status = 'error';
    $message = 'No transport selected';
} else {
    if (AlertTransport::where('transport_id', $vars['transport_id'])->delete()) {
        AlertTransportMap::where('target_type', 'single')->where('transport_or_group_id', $vars['transport_id'])->delete();
        TransportGroupTransport::where('transport_id', $vars['transport_id'])->delete();

        $message = 'Alert transport has been deleted';
    } else {
        $message = 'Alert transport has not been deleted';
    }
}

exit(json_encode([
    'status' => $status,
    'message' => $message,
]));
