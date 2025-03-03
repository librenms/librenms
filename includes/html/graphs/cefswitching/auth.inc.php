<?php

if (is_numeric($vars['id'])) {
    $cef = dbFetchRow('SELECT * FROM `cef_switching` AS C, `devices` AS D WHERE C.cef_switching_id = ? AND C.device_id = D.device_id', [$vars['id']]);

    if (is_numeric($cef['device_id']) && ($auth || device_permitted($cef['device_id']))) {
        $device = device_by_id_cache($cef['device_id']);

        $rrd_filename = Rrd::name($device['hostname'], ['cefswitching', $cef['entPhysicalIndex'], $cef['afi'], $cef['cef_index']]);

        $title = generate_device_link($device);
        $title .= ' :: CEF Switching :: ' . htmlentities($cef['cef_descr']);
        $auth = true;
    }
}
