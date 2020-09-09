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

if (!Auth::user()->hasGlobalAdmin()) {
    die(json_encode([
        'status' => 'error',
        'message' => 'You need to be admin'
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

    $maps = [];

    $devices = dbFetchRows('SELECT `device_id`, `hostname`, `sysName` FROM `transport_device_map` LEFT JOIN `devices` USING (`device_id`) WHERE `transport_id`=?', [$transport_id]);
    foreach ($devices as $device) {
        $maps[] = ['id' => $device['device_id'], 'text' => format_hostname($device)];
    }

    $groups = dbFetchRows('SELECT `group_id`, `name` FROM `transport_group_map` LEFT JOIN `device_groups` ON `device_groups`.`id`=`transport_group_map`.`group_id` WHERE `transport_id`=?', [$transport_id]);
    foreach ($groups as $group) {
        $maps[] = ['id' => 'g' . $group['group_id'], 'text' => $group['name']];
    }
    $locations = dbFetchRows('SELECT `location_id`, `location` FROM `transport_location_map` LEFT JOIN `locations` ON `locations`.`id`=`transport_location_map`.`location_id` WHERE `transport_id`=?', [$transport_id]);
    foreach ($locations as $location) {
        $maps[] = ['id' => 'l' . $location['location_id'], 'text' => $location['location']];
    }

    $details = [];
    // Get alert transport configuration details
    foreach (json_decode($transport['transport_config'], true) as $key => $value) {
        $details[] = [
            'name' => $key,
            'value' => $value
        ];
    }
}

if (is_array($transport)) {
    die(json_encode([
        'name' => $transport['transport_name'],
        'type' => $transport['transport_type'],
        'is_default' => $is_default,
        'details' => $details,
        'timerange' => ($transport['timerange'] == true) ? true : false,
        'end_timerange_hr' => ($transport['end_hr'] == null) ? '' : date("H:i", strtotime($transport['end_hr'].' UTC')),
        'start_timerange_hr' => ($transport['start_hr'] == null) ? '' : date("H:i", strtotime($transport['start_hr'].' UTC')),
        'invert_map' => ($transport['invert_map'] == true) ? true : false,
        'day' => ($transport['day'] == null) ? '' : $transport['day'],
        'maps' => $maps,
    ]));
} else {
    die(json_encode([
        'status' => 'error',
        'message' => 'No alert transport found'
    ]));
}
