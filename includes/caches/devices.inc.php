<?php

if (Auth::user()->hasGlobalRead()) {
    $data['count'] = array('query' => 'SELECT COUNT(*) FROM devices');

    $data['up'] = array('query' => "SELECT COUNT(*) FROM devices WHERE `status` = '1' AND `ignore` = '0'  AND `disabled` = '0'",);

    $data['down'] = array('query' => "SELECT COUNT(*) FROM devices WHERE `status` = '0' AND `ignore` = '0'  AND `disabled` = '0'");

    $data['ignored'] = array('query' => "SELECT COUNT(*) FROM devices WHERE `ignore` = '1' AND `disabled` = '0'");

    $data['disabled'] = array('query' => "SELECT COUNT(*) FROM devices WHERE `disabled` = '1'");
} else {
    $data['count'] = array(
        'query'  => 'SELECT COUNT(*) FROM devices AS D, devices_perms AS P WHERE P.`user_id` = ? AND P.`device_id` = D.`device_id`',
        'params' => array(Auth::id()),
    );

    $data['up'] = array(
        'query'  => "SELECT COUNT(*) FROM devices AS D, devices_perms AS P WHERE P.`user_id` = ? AND P.`device_id` = D.`device_id` AND D.`status` = '1' AND D.`ignore` = '0' AND D.`disabled` = '0'",
        'params' => array(Auth::id()),
    );

    $data['down'] = array(
        'query'  => "SELECT COUNT(*) FROM devices AS D, devices_perms AS P WHERE P.`user_id` = ? AND P.`device_id` = D.`device_id` AND D.`status` = '0' AND D.`ignore` = '0' AND D.`disabled` = '0'",
        'params' => array(Auth::id()),
    );

    $data['ignored'] = array(
        'query'  => "SELECT COUNT(*) FROM devices AS D, devices_perms AS P WHERE P.`user_id` = ? AND P.`device_id` = D.`device_id` AND D.`ignore` = '1' AND D.`disabled` = '0'",
        'params' => array(Auth::id()),
    );

    $data['disabled'] = array(
        'query'  => "SELECT COUNT(*) FROM devices AS D, devices_perms AS P WHERE P.`user_id` = ? AND P.`device_id` = D.`device_id` AND D.`disabled` = '1'",
        'params' => array(Auth::id()),
    );
}//end if
