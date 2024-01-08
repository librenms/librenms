<?php

if (Auth::user()->hasGlobalRead()) {
    $data['count'] = ['query' => 'SELECT COUNT(*) FROM devices'];

    $data['up'] = ['query' => "SELECT COUNT(*) FROM devices WHERE `status` = '1' AND `ignore` = '0'  AND `disabled` = '0'"];

    $data['down'] = ['query' => "SELECT COUNT(*) FROM devices WHERE `status` = '0' AND `ignore` = '0'  AND `disabled` = '0'"];

    $data['ignored'] = ['query' => "SELECT COUNT(*) FROM devices WHERE `ignore` = '1' AND `disabled` = '0'"];

    $data['disabled'] = ['query' => "SELECT COUNT(*) FROM devices WHERE `disabled` = '1'"];
} else {
    $device_ids = Permissions::devicesForUser()->toArray() ?: [0];
    $perms_sql = '`D`.`device_id` IN ' . dbGenPlaceholders(count($device_ids));

    $data['count'] = [
        'query'  => 'SELECT COUNT(*) FROM devices AS D WHERE $perms_sql',
        'params' => $device_ids,
    ];

    $data['up'] = [
        'query'  => "SELECT COUNT(*) FROM devices AS D WHERE $perms_sql AND D.`status` = '1' AND D.`ignore` = '0' AND D.`disabled` = '0'",
        'params' => $device_ids,
    ];

    $data['down'] = [
        'query'  => "SELECT COUNT(*) FROM devices AS D WHERE $perms_sql AND D.`status` = '0' AND D.`ignore` = '0' AND D.`disabled` = '0'",
        'params' => $device_ids,
    ];

    $data['ignored'] = [
        'query'  => "SELECT COUNT(*) FROM devices AS D WHERE $perms_sql AND D.`ignore` = '1' AND D.`disabled` = '0'",
        'params' => $device_ids,
    ];

    $data['disabled'] = [
        'query'  => "SELECT COUNT(*) FROM devices AS D WHERE $perms_sql AND D.`disabled` = '1'",
        'params' => $device_ids,
    ];
}//end if
