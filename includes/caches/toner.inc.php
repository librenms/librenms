<?php

if (Auth::user()->hasGlobalRead()) {
    $data['count'] = array('query' => "SELECT COUNT(`toner_id`) FROM toner");
} else {
    $data['count'] = array(
        'query'  => "SELECT COUNT(`toner_id`) FROM toner AS T, devices AS D, devices_perms AS P WHERE P.`user_id` = ? AND P.`device_id` = D.`device_id` AND T.`device_id` = D.`device_id`",
        'params' => array(Auth::id()),
    );
}
