<?php

use LibreNMS\Authentication\LegacyAuth;

if (LegacyAuth::user()->hasGlobalRead()) {
    $data['count'] = array('query' => "SELECT COUNT(*) FROM ports WHERE `deleted` = '0'");

    $data['up'] = array('query' => "SELECT COUNT(*) FROM ports AS I, devices AS D WHERE I.`deleted` = '0' AND D.`device_id` = I.`device_id` AND I.`ignore` = '0' AND D.`ignore` = '0' AND I.`ifOperStatus` = 'up'",);

    $data['down'] = array('query' => "SELECT COUNT(*) FROM ports AS I, devices AS D WHERE I.`deleted` = '0' AND D.`device_id` = I.`device_id` AND I.`ignore` = '0' AND D.`ignore` = '0' AND I.`ifOperStatus` = 'down' AND I.`ifAdminStatus` = 'up'");

    $data['shutdown'] = array('query' => "SELECT COUNT(*) FROM ports AS I, devices AS D WHERE I.`deleted` = '0' AND D.`device_id` = I.`device_id` AND I.`ignore` = '0' AND D.`ignore` = '0' AND I.`ifAdminStatus` = 'down'");

    $data['errored'] = array('query' => "SELECT COUNT(*) FROM ports AS I, devices AS D WHERE I.`deleted` = '0' AND D.`device_id` = I.`device_id` AND I.`ignore` = '0' AND D.`ignore` = '0' AND (I.`ifInErrors_delta` > '0' OR I.`ifOutErrors_delta` > '0')");

    $data['ignored'] = array('query' => "SELECT COUNT(*) FROM ports AS I, devices AS D WHERE I.`deleted` = '0' AND D.`device_id` = I.`device_id` AND (I.`ignore` = '1' OR D.`ignore` = '1')");
} else {
    $data['count'] = array(
        'query'  => "SELECT COUNT(*) FROM ports AS I, devices AS D, devices_perms AS P WHERE I.`deleted` = '0' AND P.`user_id` = ? AND P.`device_id` = D.`device_id` AND I.`device_id` = D.`device_id`",
        'params' => array(LegacyAuth::id()),
    );

    $data['up'] = array(
        'query'  => "SELECT COUNT(*) FROM ports AS I, devices AS D, devices_perms AS P WHERE I.`deleted` = '0' AND P.`user_id` = ? AND P.`device_id` = D.`device_id` AND I.`device_id` = D.`device_id` AND I.`ignore` = '0' AND D.`ignore` = '0' AND I.`ifOperStatus` = 'up'",
        'params' => array(LegacyAuth::id()),
    );

    $data['down'] = array(
        'query'  => "SELECT COUNT(*) FROM ports AS I, devices AS D, devices_perms AS P WHERE I.`deleted` = '0' AND P.`user_id` = ? AND P.`device_id` = D.`device_id` AND I.`device_id` = D.`device_id` AND I.`ignore` = '0' AND D.`ignore` = '0' AND I.`ifOperStatus` = 'down' AND I.`ifAdminStatus` = 'up'",
        'params' => array(LegacyAuth::id()),
    );

    $data['shutdown'] = array(
        'query'  => "SELECT COUNT(*) FROM ports AS I, devices AS D, devices_perms AS P WHERE I.`deleted` = '0' AND P.`user_id` = ? AND P.`device_id` = D.`device_id` AND I.`device_id` = D.`device_id` AND I.`ignore` = '0' AND D.`ignore` = '0' AND I.`ifAdminStatus` = 'down'",
        'params' => array(LegacyAuth::id()),
    );

    $data['errored'] = array(
        'query'  => "SELECT COUNT(*) FROM ports AS I, devices AS D, devices_perms AS P WHERE I.`deleted` = '0' AND P.`user_id` = ? AND P.`device_id` = D.`device_id` AND I.`device_id` = D.`device_id` AND I.`ignore` = '0' AND D.`ignore` = '0' AND (I.`ifInErrors_delta` > '0' OR I.`ifOutErrors_delta` > '0')",
        'params' => array(LegacyAuth::id()),
    );

    $data['ignored'] = array(
        'query'  => "SELECT COUNT(*) FROM ports AS I, devices AS D, devices_perms AS P WHERE I.`deleted` = '0' AND P.`user_id` = ? AND P.`device_id` = D.`device_id` AND I.`device_id` = D.`device_id` AND (I.`ignore` = '1' OR D.`ignore` = '1')",
        'params' => array(LegacyAuth::id()),
    );
}//end if
