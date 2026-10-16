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

use App\Models\Port;
use Illuminate\Support\Facades\Gate;

header('Content-type: application/json');

$port_id_notes = (string) ($_POST['port_id_notes'] ?? '');
$attrib_value = $_POST['notes'] ?? null;

$port = preg_match('/^port_id_notes:(\d+)$/', $port_id_notes, $matches) ? Port::with('device')->find((int) $matches[1]) : null;

if ($port === null || $port->device_id != ($_POST['device_id'] ?? null)) {
    abort(400, 'Invalid port');
}

Gate::authorize('update', $port);

$device = $port->device;

if (isset($attrib_value) && $device->setAttrib($port_id_notes, $attrib_value)) {
    $status = 'ok';
    $message = 'Updated';
} else {
    $status = 'error';
    $message = 'ERROR: Could not update';
}

echo json_encode([
    'status' => $status,
    'message' => $message,
    'attrib_type' => $port_id_notes,
    'attrib_value' => $attrib_value,
    'device_id' => $device->device_id,
]);
