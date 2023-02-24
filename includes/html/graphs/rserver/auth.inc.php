<?php

if (is_numeric($vars['id'])) {
    // $auth= TRUE;
    $rserver = dbFetchRow('SELECT * FROM `loadbalancer_rservers` AS I, `devices` AS D WHERE I.rserver_id = ? AND I.device_id = D.device_id', [$vars['id']]);

    if (is_numeric($rserver['device_id']) && ($auth || device_permitted($rserver['device_id']))) {
        $device = device_by_id_cache($rserver['device_id']);

        $rrd_filename = Rrd::name($device['hostname'], ['rserver', $rserver['rserver_id']]);

        $title = generate_device_link($device);
        $title .= ' :: Rserver :: ' . htmlentities($rserver['farm_id']);
        $auth = true;
    }
}
