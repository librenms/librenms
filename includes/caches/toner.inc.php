<?php

if (Auth::user()->hasGlobalRead()) {
    $data['count'] = ['query' => 'SELECT COUNT(`supply_id`) FROM printer_supplies'];
} else {
    $device_ids = Permissions::devicesForUser()->toArray() ?: [0];
    $perms_sql = '`printer_supplies`.`device_id` IN ' . dbGenPlaceholders(count($device_ids));

    $data['count'] = [
        'query'  => "SELECT COUNT(`supply_id`) FROM printer_supplies WHERE $perms_sql",
        'params' => $device_ids,
    ];
}
