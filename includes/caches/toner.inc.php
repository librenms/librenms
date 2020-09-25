<?php

if (Auth::user()->hasGlobalRead()) {
    $data['count'] = ['query' => 'SELECT COUNT(`toner_id`) FROM toner'];
} else {
    $device_ids = Permissions::devicesForUser()->toArray() ?: [0];
    $perms_sql = '`toner`.`device_id` IN ' . dbGenPlaceholders(count($device_ids));

    $data['count'] = [
        'query'  => "SELECT COUNT(`toner_id`) FROM toner WHERE $perms_sql",
        'params' => $device_ids,
    ];
}
