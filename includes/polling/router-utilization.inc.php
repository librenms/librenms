<?php

use LibreNMS\RRD\RrdDefinition;

$router_utilization = dbFetchRows('SELECT * FROM `router_utilization` WHERE `device_id` = ?', array($device['device_id']));

if (count($router_utilization) > 0) {
    $file = $config['install_dir'] . '/includes/polling/router-utilization/' . $device['os'] . '.inc.php';
    if (is_file($file)) {
        include $file;
    } else {
        d_echo("FILE = " . $file);
    }
} else {
    d_echo("NO DATA");
}

unset($router_utilization);
