<?php

$bgpPeers = \SnmpQuery::enumStrings()->hideMib()->walk('CUMULUS-BGPVRF-MIB::bgpPeerTable')->mapTable(
    function ($data, $vrfId, $peerIdType, $ifFace) {
        $data['vrfId'] = $vrfId;
        $data['peerIdType'] = $peerIdType;
        $data['ifIndex'] = explode('.', $ifFace)[4];

        return $data;
    });

$vrfs = DeviceCache::getPrimary()->vrfs()->select('vrf_id', 'vrf_oid')->get();
$seenPeerID = null;

foreach ($bgpPeers as $bgpPeer) {
    $bgpLocalAs = \SnmpQuery::hideMib()->get("CUMULUS-BGPVRF-MIB::bgpLocalAs.{$bgpPeer['vrfId']}")->value();
    $astext = \LibreNMS\Util\AutonomousSystem::get($bgpPeer['bgpPeerRemoteAs'])->name();
    echo "AS$bgpLocalAs \n";
    $bgpPeer['bgpPeerIdentifier'] = $bgpPeer['bgpPeerRemoteAddr'] ?? $bgpPeer['bgpPeerIdentifier']; // bgpPeerIdentifier is not unique.
    echo "BGP Peer {$bgpPeer['bgpPeerIdentifier']} ";

    $vrf = $vrfs->where('vrf_oid', $bgpPeer['vrfId'])->first();
    if (is_null($vrf)) {
        echo "VRF {$bgpPeer['vrfId']} not found, skipping peer discovery.\n";
        continue;
    }
    $vrfId = $vrf->vrf_id;

    if (! DeviceCache::getPrimary()->bgppeers()->where('bgpPeerIdentifier', $bgpPeer['bgpPeerIdentifier'])->where('vrf_id', $vrfId)->exists()) {
        $peers = [
            'vrf_id' => $vrfId,
            'bgpPeerIdentifier' => $bgpPeer['bgpPeerIdentifier'],
            'bgpPeerRemoteAs' => $bgpPeer['bgpPeerRemoteAs'],
            'bgpPeerState' => $bgpPeer['bgpPeerState'],
            'bgpPeerAdminStatus' => $bgpPeer['bgpPeerAdminStatus'],
            'bgpLocalAddr' => $bgpPeer['bgpPeerLocalAddr'],
            'bgpPeerRemoteAddr' => $bgpPeer['bgpPeerRemoteAddr'],
            'bgpPeerInUpdates' => $bgpPeer['bgpPeerInUpdates'],
            'bgpPeerOutUpdates' => $bgpPeer['bgpPeerOutUpdates'],
            'bgpPeerInTotalMessages' => $bgpPeer['bgpPeerInTotalMessages'],
            'bgpPeerOutTotalMessages' => $bgpPeer['bgpPeerOutTotalMessages'],
            'bgpPeerFsmEstablishedTime' => $bgpPeer['bgpPeerFsmEstablishedTime'],
            'bgpPeerInUpdateElapsedTime' => $bgpPeer['bgpPeerInUpdateElapsedTime'],
            'bgpPeerIface' => $bgpPeer['ifIndex'],
            'bgpPeerDescr' => $bgpPeer['bgpPeerDesc'],
            'astext' => $astext,
        ];

        DeviceCache::getPrimary()->bgppeers()->create($peers);

        if (Config::get('autodiscovery.bgp')) {
            $name = gethostbyaddr($bgpPeer['bgpPeerRemoteAddr']);
            discover_new_device($name, $device, 'BGP');
        }
        echo '+';
    } else {
        $peers = [
            'bgpPeerRemoteAs' => $bgpPeer['bgpPeerRemoteAs'],
            'astext' => $astext,
        ];
        $affected = DeviceCache::getPrimary()->bgppeers()->where('bgpPeer_id', $bgpPeer['bgpPeerIdentifier'])->update($peers);
        echo str_repeat('.', $affected);
    }
    $seenPeerID[] = DeviceCache::getPrimary()->bgppeers()->where('bgpPeerIdentifier', $bgpPeer['bgpPeerIdentifier'])->where('vrf_id', $vrfId)->select('bgpPeer_id')->orderBy('bgpPeer_id', 'ASC')->first()->bgpPeer_id;
}

if (! is_null($seenPeerID)) {
    $deleted = DeviceCache::getPrimary()->bgppeers()->whereNotIn('bgpPeer_id', $seenPeerID)->delete();
    echo str_repeat('-', $deleted);
}

unset($bgpPeers);
