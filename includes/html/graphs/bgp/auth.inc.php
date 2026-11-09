<?php

if (is_numeric($vars['id'])) {
    $data = dbFetchRow('SELECT * FROM bgpPeers WHERE bgpPeer_id = ?', [$vars['id']]);

    if (is_numeric($data['device_id']) && ($auth || device_permitted($data['device_id']))) {
        $device = DeviceCache::get($data['device_id']);

        $title = ' :: BGP :: ' . $data['bgpPeerIdentifier'];
        $auth = true;
    }
}
