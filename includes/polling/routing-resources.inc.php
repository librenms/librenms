<?php

use LibreNMS\RRD\RrdDefinition;

$routing_resources = dbFetchRows('SELECT * FROM `routing_resources` WHERE `device_id` = ?', array($device['device_id']));

if (count($routing_resources) > 0) {
    $file = $config['install_dir'] . '/includes/polling/routing-resources/' . $device['os'] . '.inc.php';
    if (is_file($file)) {
        include $file;
    } else {
        d_echo("FILE = " . $file);
    }
} else {
    d_echo("NO DATA");
}

unset($routing_resources);
