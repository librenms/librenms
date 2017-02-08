<?php

$os   = getHostOS($device);
if ($os != $device['os']) {
    log_event('Device OS changed '.$device['os']." => $os", $device, 'system');
    $device['os'] = $os;
    $sql = dbUpdate(array('os' => $os), 'devices', 'device_id=?', array($device['device_id']));
    echo "Changed OS! : $os\n";
}

$icon = getImageName($device, false);
if ($icon != $device['icon']) {
    log_event('Device Icon changed '.$device['icon']." => $icon", $device, 'system');
    $device['icon'] = $icon;
    $sql = dbUpdate(array('icon' => $icon), 'devices', 'device_id=?', array($device['device_id']));
    echo "Changed Icon! : $icon\n";
}
