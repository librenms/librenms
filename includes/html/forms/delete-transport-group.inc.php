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

use App\Models\AlertTransport;
use App\Models\AlertTransportGroup;
use App\Models\AlertTransportMap;
use App\Models\TransportGroupTransport;
use Illuminate\Support\Facades\Gate;

header('Content-type: application/json');

if (Gate::denies('update', AlertTransport::class)) {
    exit(json_encode([
        'status' => 'error',
        'message' => 'ERROR: You need permission.',
    ]));
}

$status = 'ok';
$message = '';

if (! is_numeric($vars['group_id'])) {
    $status = 'error';
    $message = 'ERROR: No transport group selected';
} else {
    if (AlertTransportGroup::where('transport_group_id', $vars['group_id'])->delete()) {
        TransportGroupTransport::where('transport_group_id', $vars['group_id'])->delete();
        AlertTransportMap::where('target_type', 'group')->where('transport_or_group_id', $vars['group_id'])->delete();
        $message = 'Alert transport group has been deleted';
    } else {
        $message = 'ERROR: Alert transport group has not been deleted';
    }
}

exit(json_encode([
    'status' => $status,
    'message' => $message,
]));
