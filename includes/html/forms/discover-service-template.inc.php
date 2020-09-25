<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2016 Aaron Daniels <aaron@daniels.id.au>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

if (! Auth::user()->hasGlobalAdmin()) {
    exit('ERROR: You need to be admin');
}

$service_template_id = $vars['service_template_id'];
$device_group_id = $vars['device_group_id'];

if (is_numeric($service_template_id) && is_numeric($device_group_id)) {
    if (discover_service_template($device_group_id, $service_template_id)) {
        $status = ['status' =>0, 'message' => 'Device Group: <i>' . $device_group_id . ', Service Template: <i>' . $service_template_id . ', has been discovered.</i>'];
    } else {
        $status = ['status' =>1, 'message' => 'Device Group: <i>' . $device_group_id . ', Service Template: <i>' . $service_template_id . ', has NOT been discovered.</i>'];
    }
}
header('Content-Type: application/json');
echo _json_encode($status);
