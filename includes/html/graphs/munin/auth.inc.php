<?php

if (is_numeric($vars['plugin'])) {
    $mplug = dbFetchRow('SELECT * FROM `munin_plugins` AS M, `devices` AS D WHERE `mplug_id` = ? AND D.device_id = M.device_id ', [$vars['plugin']]);
} else {
    $mplug = dbFetchRow('SELECT * FROM `munin_plugins` AS M, `devices` AS D WHERE M.`device_id` = ? AND `mplug_type` = ?  AND D.device_id = M.device_id', [$device['device_id'], $vars['plugin']]);
}

if (is_numeric($mplug['device_id']) && ($auth || device_permitted($mplug['device_id']))) {
    $device = &$mplug;
    $title = generate_device_link($device);
    $title .= ' :: Plugin :: ' . $mplug['mplug_type'] . ' - ' . $mplug['mplug_title'];
    $auth = true;
}
