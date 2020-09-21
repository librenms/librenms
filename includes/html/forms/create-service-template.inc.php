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
$type = $vars['stype'];
$desc = $vars['desc'];
$ip = $vars['ip'];
$param = $vars['param'];
$ignore = isset($vars['ignore']) ? 1 : 0;
$disabled = isset($vars['disabled']) ? 1 : 0;


if (is_numeric($service_template_id) && $service_template_id > 0) {
    // Need to edit.
    $update = ['device_group_id' => $device_group_id, 'service_template_type' => $type, 'service_template_desc' => $desc, 'service_template_ip' => $ip, 'service_template_param' => $param, 'service_template_ignore' => $ignore, 'service_template_disabled' => $disabled];
    if (is_numeric(edit_service_template($update, $service_template_id))) {
        $status = array('status' =>0, 'message' => 'Modified Service Template: <i>'.$service_template_id.': '.$type.'</i>');
    } else {
        $status = array('status' =>1, 'message' => 'ERROR: Failed to modify Service Template: <i>'.$service_template_id.'</i>');
    }
} else {
    // Need to add.
    $service_template_id = add_service_template($device_group_id, $type, $desc, $ip, $param, $ignore, $disabled);
    if ($service_template_id == null) {
        $status = ['status' =>1, 'message' => 'ERROR: Failed to add Service Template: <i>'.$service_template_id.': '.$type.'</i>'];
    } else {
        $status = ['status' =>0, 'message' => 'Added Service Template: <i>'.$service_template_id.': '.$type.'</i>'];
    }
}
header('Content-Type: application/json');
echo _json_encode($status);
