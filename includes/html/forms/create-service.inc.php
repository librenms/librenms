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

if (!Auth::user()->hasGlobalAdmin()) {
    die('ERROR: You need to be admin');
}

$service_id = $vars['service_id'];
$type = $vars['stype'];
$desc = $vars['desc'];
$ip = $vars['ip'];
$param = $vars['param'];
$ignore = isset($vars['ignore']) ? 1 : 0;
$disabled = isset($vars['disabled']) ? 1 : 0;
$device_id = $vars['device_id'];

if (is_numeric($service_id) && $service_id > 0) {
    // Need to edit.
    $update = array('service_desc' => $desc, 'service_ip' => $ip, 'service_param' => $param, 'service_ignore' => $ignore, 'service_disabled' => $disabled);
    if (is_numeric(edit_service($update, $service_id))) {
        $status = array('status' =>0, 'message' => 'Modified Service: <i>'.$service_id.': '.$type.'</i>');
    } else {
        $status = array('status' =>1, 'message' => 'ERROR: Failed to modify service: <i>'.$service_id.'</i>');
    }
} else {
    // Need to add.
    $service_id = add_service($device_id, $type, $desc, $ip, $param, $ignore, $disabled);
    if ($service_id == false) {
        $status = array('status' =>1, 'message' => 'ERROR: Failed to add Service: <i>'.$type.'</i>');
    } else {
        $status = array('status' =>0, 'message' => 'Added Service: <i>'.$service_id.': '.$type.'</i>');
    }
}
header('Content-Type: application/json');
echo _json_encode($status);
