<?php

if (Auth::user()->hasGlobalRead()) {
    $data['count'] = array('query' => "SELECT COUNT(*) FROM ports WHERE `deleted` = '0'");

    $data['up'] = array('query' => "SELECT COUNT(*) FROM ports AS I, devices AS D WHERE I.`deleted` = '0' AND D.`device_id` = I.`device_id` AND I.`ignore` = '0' AND D.`ignore` = '0' AND I.`ifOperStatus` = 'up'",);

    $data['down'] = array('query' => "SELECT COUNT(*) FROM ports AS I, devices AS D WHERE I.`deleted` = '0' AND D.`device_id` = I.`device_id` AND I.`ignore` = '0' AND D.`ignore` = '0' AND I.`ifOperStatus` <> 'up' AND I.`ifAdminStatus` = 'up'");

    $data['shutdown'] = array('query' => "SELECT COUNT(*) FROM ports AS I, devices AS D WHERE I.`deleted` = '0' AND D.`device_id` = I.`device_id` AND I.`ignore` = '0' AND D.`ignore` = '0' AND I.`ifAdminStatus` = 'down'");

    $data['errored'] = array('query' => "SELECT COUNT(*) FROM ports AS I, devices AS D WHERE I.`deleted` = '0' AND D.`device_id` = I.`device_id` AND I.`ignore` = '0' AND D.`ignore` = '0' AND (I.`ifInErrors_delta` > '0' OR I.`ifOutErrors_delta` > '0')");

    $data['ignored'] = array('query' => "SELECT COUNT(*) FROM ports AS I, devices AS D WHERE I.`deleted` = '0' AND D.`device_id` = I.`device_id` AND (I.`ignore` = '1' OR D.`ignore` = '1')");
} else {
    $device_ids = Permissions::portsForUser()->toArray() ?: [0];
    $perms_sql = "`I`.`port_id` IN " .dbGenPlaceholders(count($device_ids));

    $data['count'] = array(
        'query'  => "SELECT COUNT(*) FROM ports AS I WHERE $perms_sql AND I.`deleted` = '0'",
        'params' => $device_ids
    );

    $data['up'] = array(
        'query'  => "SELECT COUNT(*) FROM ports AS I, devices AS D WHERE $perms_sql AND I.`deleted` = '0' AND I.`device_id` = D.`device_id` AND I.`ignore` = '0' AND D.`ignore` = '0' AND I.`ifOperStatus` = 'up'",
        'params' => $device_ids
    );

    $data['down'] = array(
        'query'  => "SELECT COUNT(*) FROM ports AS I, devices AS D WHERE $perms_sql AND I.`deleted` = '0' AND I.`device_id` = D.`device_id` AND I.`ignore` = '0' AND D.`ignore` = '0' AND I.`ifOperStatus` <> 'up' AND I.`ifAdminStatus` = 'up'",
        'params' => $device_ids
    );

    $data['shutdown'] = array(
        'query'  => "SELECT COUNT(*) FROM ports AS I, devices AS D WHERE $perms_sql AND I.`deleted` = '0' AND I.`device_id` = D.`device_id` AND I.`ignore` = '0' AND D.`ignore` = '0' AND I.`ifAdminStatus` = 'down'",
        'params' => $device_ids
    );

    $data['errored'] = array(
        'query'  => "SELECT COUNT(*) FROM ports AS I, devices AS D WHERE $perms_sql AND I.`deleted` = '0' AND I.`device_id` = D.`device_id` AND I.`ignore` = '0' AND D.`ignore` = '0' AND (I.`ifInErrors_delta` > '0' OR I.`ifOutErrors_delta` > '0')",
        'params' => $device_ids
    );

    $data['ignored'] = array(
        'query'  => "SELECT COUNT(*) FROM ports AS I, devices AS D WHERE $perms_sql AND I.`deleted` = '0' AND I.`device_id` = D.`device_id` AND (I.`ignore` = '1' OR D.`ignore` = '1')",
        'params' => $device_ids
    );
}//end if
