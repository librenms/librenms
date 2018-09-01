<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2018 PipoCanaja <pipocanaja@gmail.com>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

header('Content-type: application/json');

use LibreNMS\Config;
use LibreNMS\Authentication\Auth;

$device_hostname = clean($_POST['device_hostname']);
if (Auth::user()->hasGlobalAdmin() && isset($device_hostname)) {
    if (oxidized_node_update($device_hostname, "LibreNMS GUI refresh", $_SESSION['username'])) {
        $status  = 'ok';
        $message = 'Queued refresh in oxidized for device ' . $device_hostname;
    } else {
        $status  = 'error';
        $message = 'ERROR: Could not queue refresh of oxidized device' . $device_hostname;
    };
} else {
    $status  = 'error';
    $message = 'ERROR: Could not queue refresh oxidized device';
}

$output = array(
    'status'  => $status,
    'message' => $message,
);

header('Content-type: application/json');
echo _json_encode($output);


function oxidized_node_update($hostname, $msg, $username = '')
{
    $postdata = ["user" => $username, "msg" => $msg];
    $oxidized_url = Config::get('oxidized.url');
    if (!empty($oxidized_url)) {
        Requests::put("$oxidized_url/node/next/$hostname", [], json_encode($postdata), ['proxy' => get_proxy()]);
        return true;
    } else {
        return false;
    }
}//end oxidized_node_update()
