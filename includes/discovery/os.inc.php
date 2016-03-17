<?php

echo 'OS: ';

$os   = getHostOS($device);
if ($os != $device['os']) {
    $device['os'] = $os;
    $sql = dbUpdate(array('os' => $os), 'devices', 'device_id=?', array($device['device_id']));
    echo "Changed OS! : $os\n";
    log_event('Device OS changed '.$device['os']." => $os", $device, 'system');
}

$icon = getImageName($device, false);
if ($icon != $device['icon']) {
    $device['icon'] = $icon;
    $sql = dbUpdate(array('icon' => $icon), 'devices', 'device_id=?', array($device['device_id']));
    echo "Changed Icon! : $icon\n";
    log_event('Device Icon changed '.$device['icon']." => $icon", $device, 'system');
}
