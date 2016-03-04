<?php

echo 'OS: ';

// MYSQL Check - FIXME
// 1 UPDATE
$os = getHostOS($device);
if ($os != $device['os'] || empty($device['icon'])) {
    $device['os'] = $os;

    // update icon
    $icon = getImageName($device, false);
    $device['icon'] = $icon;


    $sql = dbUpdate(array('os' => $os, 'icon' => $icon), 'devices', 'device_id=?', array($device['device_id']));
    echo "Changed OS! : $os\n";
    log_event('Device OS changed '.$device['os']." => $os", $device, 'system');
}
