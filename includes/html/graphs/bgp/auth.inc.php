<?php

use Illuminate\Support\Facades\Auth;

if (is_numeric($vars['id'])) {
    $data = dbFetchRow('SELECT * FROM bgpPeers WHERE bgpPeer_id = ?', [$vars['id']]);

    if (is_numeric($data['device_id']) && ($auth || Auth::user()->canAccessDevice($data['device_id']))) {
        $device = device_by_id_cache($data['device_id']);

        $title = generate_device_link($device);
        $title .= ' :: BGP :: ' . htmlentities($data['bgpPeerIdentifier']);
        $auth = true;
    }
}
