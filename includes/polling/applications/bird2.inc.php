<?php

use App\Models\BgpPeer;
use Carbon\Carbon;
use LibreNMS\Util\Bird2;
use LibreNMS\Util\Oid;

$name = 'bird2';

$birdOutput = SnmpQuery::get('NET-SNMP-EXTEND-MIB::nsExtendOutputFull.' . Oid::encodeString($name))->value();

// make sure we actually get something back
if (empty($birdOutput)) {
    echo PHP_EOL . $name . ': has empty output' . PHP_EOL;

    return;
}

// ========
// Process the actual BIRD2 output
$protocolsData = Bird2::parseProtocols((string) $birdOutput);

// ---
$deviceObj = DeviceCache::getPrimary();

if (empty($protocolsData)) {
    echo PHP_EOL . $name . ': No BGP Peers found' . PHP_EOL;
    $deviceObj->bgpLocalAs = null;
    $deviceObj->save();

    return;
}

// Do bgpLocalAs Update
// Get the most common localAS (in theory there *should* be only one, but not always
$localAsns = array_count_values(array_column($protocolsData, 'local_as'));
arsort($localAsns);
$bgpLocalAs = array_keys($localAsns)[0];

$deviceObj->bgpLocalAs = $bgpLocalAs;
$deviceObj->save();

// Going through all BGP Peers
$bgpPeerIds = [];

foreach ($protocolsData as $protocol) {
    // Skip peers that don't have neighbor_address (incomplete BGP handshakes/errors)
    if (! isset($protocol['neighbor_address']) || ! isset($protocol['neighbor_as'])) {
        echo PHP_EOL . $name . ': Skipped peer ' . $protocol['name'] . ' (incomplete BGP data)';
        continue;
    }

    $bgpPeer = BgpPeer::firstOrNew([
        'device_id' => $device['device_id'],
        'bgpPeerRemoteAs' => $protocol['neighbor_as'],
        'bgpLocalAddr' => $protocol['source_address'] ?? '0.0.0.0',
        'bgpPeerRemoteAddr' => $protocol['neighbor_address'],
    ]);

    $bgpPeer->device_id = $device['device_id'];
    $bgpPeer->astext = \LibreNMS\Util\AutonomousSystem::get($protocol['neighbor_as'])->name();
    $bgpPeer->bgpPeerIdentifier = $protocol['neighbor_id'] ?? '0.0.0.0';
    $bgpPeer->bgpPeerRemoteAs = $protocol['neighbor_as'];
    $bgpPeer->bgpPeerState = strtolower((string) $protocol['bgp_state']);
    $bgpPeer->bgpPeerAdminStatus = str_replace('up', 'start', strtolower((string) $protocol['protocol_state']));

    if (isset($protocolData['last_error'])) {
        // Find the subcode if its there and set it
        foreach (trans('bgp.error_subcodes') as $mainCode => $subCodes) {
            foreach ($subCodes as $subCode => $message) {
                if ($message == $protocolData['last_error']) {
                    $bgpPeer->bgpPeerLastErrorCode = $mainCode;
                    $bgpPeer->bgpPeerLastErrorSubCode = $subCode;
                }
            }
        }

        $bgpPeer->bgpPeerLastErrorText = $protocol['neighbor_id'] ?? '0.0.0.0';
    }

    $bgpPeer->bgpLocalAddr = $protocol['source_address'] ?? '0.0.0.0';
    $bgpPeer->bgpPeerRemoteAddr = $protocol['neighbor_address'];
    $bgpPeer->bgpPeerDescr = $protocol['description'] ?: $protocol['name'];
    $bgpPeer->bgpPeerInUpdates = intval($protocol['route_change_stats']['import_updates']['accepted'] ?? 0);
    $bgpPeer->bgpPeerOutUpdates = intval($protocol['route_change_stats']['export_updates']['accepted'] ?? 0);
    $bgpPeer->bgpPeerInTotalMessages = intval($protocol['route_change_stats']['import_updates']['received'] ?? 0);
    $bgpPeer->bgpPeerOutTotalMessages = intval($protocol['route_change_stats']['export_updates']['received'] ?? 0);

    $bgpPeer->bgpPeerFsmEstablishedTime = (int) Carbon::parse($protocol['since'])->diffInSeconds(Carbon::now(), true);
    $bgpPeer->bgpPeerInUpdateElapsedTime = (int) Carbon::parse($protocol['since'])->diffInSeconds(Carbon::now(), true);
    $bgpPeer->save();

    echo PHP_EOL . $name . ': Processed peer AS' . $bgpPeer->bgpPeerRemoteAs . ' (' . $bgpPeer->astext . ')';

    $bgpPeerIds[] = $bgpPeer->bgpPeer_id;
}

echo PHP_EOL;

// Clean up any bgpPeers that arent on the list for this device
BgpPeer::where('device_id', $device['device_id'])->whereNotIn('bgpPeer_id', $bgpPeerIds)->delete();
