<?php

if ($_SESSION['userlevel'] >= 5) {
    $data['count']    = array( 'query' => 'SELECT COUNT(*) FROM services');
    $data['up']       = array( 'query' => "SELECT COUNT(*) FROM services WHERE `service_ignore` = '0' AND `service_disabled` = '0' AND `service_status` = '0'");
    $data['down']     = array( 'query' => "SELECT COUNT(*) FROM services WHERE `service_ignore` = '0' AND `service_disabled` = '0' AND `service_status` = '2'");
    $data['ignored']  = array( 'query' => "SELECT COUNT(*) FROM services WHERE `service_ignore` = '1' AND `service_disabled` = '0'");
    $data['disabled'] = array( 'query' => "SELECT COUNT(*) FROM services WHERE `service_disabled` = '1'");
} else {
    $data['count'] = array(
        'query'  => 'SELECT COUNT(*) FROM services AS S, devices AS D, devices_perms AS P WHERE P.`user_id` = ? AND P.`device_id` = D.`device_id` AND S.`device_id` = D.`device_id`',
        'params' => array($_SESSION['user_id']),
    );

    $data['up'] = array(
        'query'  => "SELECT COUNT(*) FROM services AS S, devices AS D, devices_perms AS P WHERE P.`user_id` = ? AND P.`device_id` = D.`device_id` AND S.`device_id` = D.`device_id` AND S.`service_ignore` = '0' AND S.`service_disabled` = '0' AND S.`service_status` = '0'",
        'params' => array($_SESSION['user_id']),
    );

    $data['down'] = array(
        'query'  => "SELECT COUNT(*) FROM services AS S, devices AS D, devices_perms AS P WHERE P.`user_id` = ? AND P.`device_id` = D.`device_id` AND S.`device_id` = D.`device_id` AND S.`service_ignore` = '0' AND S.`service_disabled` = '0' AND S.`service_status` = '2'",
        'params' => array($_SESSION['user_id']),
    );

    $data['ignored'] = array(
        'query'  => "SELECT COUNT(*) FROM services AS S, devices AS D, devices_perms AS P WHERE P.`user_id` = ? AND P.`device_id` = D.`device_id` AND S.`device_id` = D.`device_id` AND S.`service_ignore` = '1' AND S.`service_disabled` = '0'",
        'params' => array($_SESSION['user_id']),
    );

    $data['disabled'] = array(
        'query'  => "SELECT COUNT(*) FROM services AS S, devices AS D, devices_perms AS P WHERE P.`user_id` = ? AND P.`device_id` = D.`device_id` AND S.`device_id` = D.`device_id` AND S.`service_disabled` = '1'",
        'params' => array($_SESSION['user_id']),
    );
}//end if
