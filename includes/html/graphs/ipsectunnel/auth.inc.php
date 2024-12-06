<?php

if (is_numeric($vars['id'])) {
    $tunnel = dbFetchRow('SELECT * FROM `ipsec_tunnels` AS I, `devices` AS D WHERE I.tunnel_id = ? AND I.device_id = D.device_id', [$vars['id']]);

    if (is_numeric($tunnel['device_id']) && ($auth || device_permitted($tunnel['device_id']))) {
        $device = device_by_id_cache($tunnel['device_id']);

        $rrd_filename = Rrd::name($device['hostname'], ['ipsectunnel', $tunnel['peer_addr']]);

        $title = generate_device_link($device);
        $title .= ' :: IPSEC Tunnel :: ' . htmlentities($tunnel['peer_addr']);
        $auth = true;
    }
}
