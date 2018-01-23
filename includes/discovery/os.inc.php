<?php

use LibreNMS\Config;

$os = getHostOS($device);

if ($os != $device['os']) {
    log_event('Device OS changed ' . $device['os'] . " => $os", $device, 'system', 3);
    $device['os'] = $os;
    $sql = dbUpdate(array('os' => $os), 'devices', 'device_id=?', array($device['device_id']));

    load_os($device);
    load_discovery($device);

    echo "Changed ";
}

echo "OS: " . Config::getOsSetting($os, 'text') . " ($os)\n";

update_device_logo($device);
