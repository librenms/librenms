<?php

use LibreNMS\Config;
use LibreNMS\OS;

$os_name = getHostOS($device);

if ($os_name != $device['os']) {
    log_event('Device OS changed ' . $device['os'] . " => $os_name", $device, 'system', 3);
    $device['os'] = $os_name;
    $sql = dbUpdate(array('os' => $os_name), 'devices', 'device_id=?', array($device['device_id']));

    load_os($device);
    load_discovery($device);
    $os = OS::make($device);

    echo "Changed ";
}

echo "OS: " . Config::getOsSetting($os_name, 'text') . " ($os_name)\n";

update_device_logo($device);
