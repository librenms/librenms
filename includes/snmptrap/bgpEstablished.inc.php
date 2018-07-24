<?php

$bgppeerip = strstr(strstr($entry[5], " ", true), ".");
$bgppeerip = substr($bgppeerip, 1);

$bgppeer = dbFetchRow("SELECT * FROM `bgpPeers` WHERE `device_id` = ? AND `bgpPeerIdentifier` = ?", array($device['device_id'],$bgppeerip));

if (!$bgppeer) {
    echo "unknow peer ($bgppeerip)\n";
    exit;
}

$bgpstatus = trim(strstr($entry[5], " "));

log_event('SNMP Trap: BGP Up ' . $bgppeer['bgpPeerIdentifier'] . ' ' . get_astext($bgppeer['bgpPeerRemoteAs']) . ' is now ' . $bgpstatus, $device, 'bgpPeer', 1, $bgppeerip);

dbUpdate(array('bgpPeerState' => $bgpstatus), 'bgpPeers', 'bgpPeer_id=?', array($bgppeer['bgpPeer_id']));

unset($bgppeerip, $bgppeer, $bgpstatus);
