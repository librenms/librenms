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
    $status = array('status' =>1, 'message' => 'ERROR: You need to be admin to apply service templates');
} else {
    if (!is_numeric($vars['service_template_id'])) {
        $status = array('status' =>1, 'message' => 'No Service Template has been selected');
    } elseif (!is_numeric($vars['device_group_id'])) {
            $status = array('status' =>1, 'message' => 'No Device Group has been selected');
    } else {
        if (discover_service_template($vars['device_group_id'],$vars['service_template_id'])) {
            $status = array('status' =>0, 'message' => 'Device Group: <i>'.$vars['device_group_id'].',Service Template: <i>'.$vars['service_template_id'].', has been discovered.</i>');
        } else {
            $status = array('status' =>1, 'message' => 'Device Group: <i>'.$vars['device_group_id'].',Service Template: <i>'.$vars['service_template_id'].', has been discovered.</i>');
        }
    }
}
header('Content-Type: application/json');
echo _json_encode($status);
