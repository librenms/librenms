<?php

if (Auth::user()->hasGlobalRead()) {
    $data['count']    = array( 'query' => 'SELECT COUNT(*) FROM services');
    $data['up']       = array( 'query' => "SELECT COUNT(*) FROM services WHERE `service_ignore` = '0' AND `service_disabled` = '0' AND `service_status` = '0'");
    $data['down']     = array( 'query' => "SELECT COUNT(*) FROM services WHERE `service_ignore` = '0' AND `service_disabled` = '0' AND `service_status` = '2'");
    $data['ignored']  = array( 'query' => "SELECT COUNT(*) FROM services WHERE `service_ignore` = '1' AND `service_disabled` = '0'");
    $data['disabled'] = array( 'query' => "SELECT COUNT(*) FROM services WHERE `service_disabled` = '1'");
} else {
    $device_ids = Permissions::devicesForUser()->toArray() ?: [0];
    $perms_sql = "`S`.`device_id` IN " .dbGenPlaceholders(count($device_ids));

    $data['count'] = array(
        'query'  => 'SELECT COUNT(*) FROM services AS S WHERE $perms_sql',
        'params' => $device_ids
    );

    $data['up'] = array(
        'query'  => "SELECT COUNT(*) FROM services AS S WHERE $perms_sql AND S.`service_ignore` = '0' AND S.`service_disabled` = '0' AND S.`service_status` = '0'",
        'params' => $device_ids
    );

    $data['down'] = array(
        'query'  => "SELECT COUNT(*) FROM services AS S WHERE $perms_sql AND S.`service_ignore` = '0' AND S.`service_disabled` = '0' AND S.`service_status` = '2'",
        'params' => $device_ids
    );

    $data['ignored'] = array(
        'query'  => "SELECT COUNT(*) FROM services AS S WHERE $perms_sql AND S.`service_ignore` = '1' AND S.`service_disabled` = '0'",
        'params' => $device_ids
    );

    $data['disabled'] = array(
        'query'  => "SELECT COUNT(*) FROM services AS S WHERE $perms_sql AND S.`service_disabled` = '1'",
        'params' => $device_ids
    );
}//end if
