<?php

use App\Facades\LibrenmsConfig;
use Illuminate\Support\Facades\Log;
use LibreNMS\Util\ProgressList;

$bgpPeerTable = \SnmpQuery::enumStrings()->hideMib()->walk('CUMULUS-BGPVRF-MIB::bgpPeerTable');
if (! $bgpPeerTable->isValid() && ! $bgpPeerTable->isEmpty()) {
    // a walk cut short (timeout) misses columns: keep the stored peers and the local AS as they are
    // the shared BGP4-MIB pass of includes/discovery/bgp-peers.inc.php still runs after this return
    Log::warning('CUMULUS-BGPVRF-MIB::bgpPeerTable walk failed, BGP peers not updated: ' . $bgpPeerTable->getErrorMessage());
    $bgpLocalAs = $device['bgpLocalAs'];

    return;
}

$bgpPeers = $bgpPeerTable->mapTable(
    function ($data, $vrfId) {
        $data['vrfId'] = $vrfId;
        $data['ifIndex'] = ($data['bgpPeerIfindex'] ?? 0) ?: null; // 0 when the session is not on an interface

        return $data;
    });

$vrfs = DeviceCache::getPrimary()->vrfs()->select('vrf_id', 'vrf_oid')->get();
$seenPeerID = null;

foreach ($bgpPeers as $bgpPeer) {
    $bgpLocalAs = \SnmpQuery::hideMib()->get("CUMULUS-BGPVRF-MIB::bgpLocalAs.{$bgpPeer['vrfId']}")->value();
    $astext = \LibreNMS\Util\AutonomousSystem::get($bgpPeer['bgpPeerRemoteAs'])->name();
    Log::info("AS$bgpLocalAs");
    $bgpPeer['bgpPeerIdentifier'] = $bgpPeer['bgpPeerRemoteAddr'] ?? $bgpPeer['bgpPeerIdentifier']; // bgpPeerIdentifier is not unique.
    $vrf = $vrfs->where('vrf_oid', $bgpPeer['vrfId'])->first();
    if (is_null($vrf)) {
        Log::info("BGP Peer {$bgpPeer['bgpPeerIdentifier']} VRF {$bgpPeer['vrfId']} not found, skipping peer discovery.");
        continue;
    }
    $progress = ProgressList::start("BGP Peer {$bgpPeer['bgpPeerIdentifier']}");
    $vrfId = $vrf->vrf_id;

    if (! DeviceCache::getPrimary()->bgppeers()->where('bgpPeerIdentifier', $bgpPeer['bgpPeerIdentifier'])->where('vrf_id', $vrfId)->exists()) {
        $peers = [
            'vrf_id' => $vrfId,
            'bgpPeerIdentifier' => $bgpPeer['bgpPeerIdentifier'],
            'context_name' => '',
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

        // Only reverse-resolve / autodiscover when there is a real peer IP. Unnumbered
        // peers that have never established report bgpPeerRemoteAddr = "Unknown".
        if (LibrenmsConfig::get('autodiscovery.bgp') && filter_var($bgpPeer['bgpPeerRemoteAddr'], FILTER_VALIDATE_IP)) {
            $name = gethostbyaddr($bgpPeer['bgpPeerRemoteAddr']);
            discover_new_device($name, $device, 'BGP');
        }
        $progress->mark('+');
    } else {
        $peers = [
            'bgpPeerRemoteAs' => $bgpPeer['bgpPeerRemoteAs'],
            'bgpPeerIface' => $bgpPeer['ifIndex'],
            'astext' => $astext,
        ];
        $affected = DeviceCache::getPrimary()->bgppeers()->where('bgpPeerIdentifier', $bgpPeer['bgpPeerIdentifier'])->where('vrf_id', $vrfId)->update($peers);
        $progress->mark(str_repeat('.', $affected));
    }
    $seenPeerID[] = DeviceCache::getPrimary()->bgppeers()->where('bgpPeerIdentifier', $bgpPeer['bgpPeerIdentifier'])->where('vrf_id', $vrfId)->select('bgpPeer_id')->orderBy('bgpPeer_id', 'ASC')->first()->bgpPeer_id;
    $progress->end();
}

if (! is_null($seenPeerID)) {
    $deleted = DeviceCache::getPrimary()->bgppeers()->whereNotIn('bgpPeer_id', $seenPeerID)->delete();
    $progress = ProgressList::start('Deleted');
    $progress->mark(str_repeat('-', $deleted));
    $progress->end();
}

unset($bgpPeers);
