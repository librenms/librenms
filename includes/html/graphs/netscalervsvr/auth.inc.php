<?php

if (is_numeric($vars['id'])) {
    $vsvr = dbFetchRow('SELECT * FROM `netscaler_vservers` AS I, `devices` AS D WHERE I.vsvr_id = ? AND I.device_id = D.device_id', [$vars['id']]);

    if (is_numeric($vsvr['device_id']) && ($auth || device_permitted($vsvr['device_id']))) {
        $device = device_by_id_cache($vsvr['device_id']);

        $rrd_filename = Rrd::name($device['hostname'], ['netscaler', 'vsvr', $vsvr['vsvr_name']]);

        $title = generate_device_link($device);
        $title .= ' :: Netscaler VServer :: ' . htmlentities($vsvr['vsvr_name']);
        $auth = true;
    }
}
