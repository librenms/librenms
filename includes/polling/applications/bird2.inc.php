<?php

use App\Models\BgpPeer;
use Carbon\Carbon;

$name = 'bird2';

$birdOutput = snmp_get($device, 'nsExtendOutputFull.' . \LibreNMS\Util\Oid::ofString($name), '-Oqv', 'NET-SNMP-EXTEND-MIB');

// make sure we actually get something back
if (empty($birdOutput)) {
    echo PHP_EOL . $name . ': has empty output' . PHP_EOL;

    return;
}

// ========
// Process the actual BIRD2 output
$protocolsData = [];

// Remove headers
$birdOutput = trim(explode('Name       Proto      Table      State  Since         Info', $birdOutput, 2)[1]);
$protocolSegments = explode("\n\n", $birdOutput);

// Remove the first title
unset($protocolSegments[0]);

foreach ($protocolSegments as $protocolSegment) {
    // Deal with the title first
    $protocolSegmentParts = explode("\n", $protocolSegment, 2);
    $titleParts = preg_split("/\s+/", $protocolSegmentParts[0], 5);

    // make sure we only look at BGP protocols
    if ($titleParts[1] !== 'BGP') {
        continue;
    }

    $protocolData = [
        'name' => $titleParts[0],
        'type' => $titleParts[1],
        'table' => $titleParts[2],
        'protocol_state' => $titleParts[3],
        'since' => preg_split("/\s+/", $titleParts[4], 3)[0] . ' ' . preg_split("/\s+/", $titleParts[4], 3)[1],
    ];

    // Deal with the rest of the body
    $protocolBodys = preg_split("/^\s{2}([A-Z])/m", $protocolSegmentParts[1]);

    // Loop through all BGP protocols
    foreach ($protocolBodys as $protocolBody) {
        // Deal with the BGP block
        if (strpos($protocolBody, 'GP') === 0) {
            foreach (explode("\n", 'B' . $protocolBody) as $protocolBodyLine) {
                if (strpos($protocolBodyLine, ':') !== false) {
                    $lineParts = explode(':', $protocolBodyLine, 2);
                    $protocolData[str_replace(' ', '_', strtolower(trim($lineParts[0])))] = trim($lineParts[1]);
                }
            }

            // Fix up the error string
            if (isset($protocolData['last_error'])) {
                // Trim the received
                $protocolData['last_error'] = trim(str_ireplace('Received:', '', $protocolData['last_error']));
            }
        }

        // Process the Ip channel (v4/v6)
        $IpVersion = 4;
        if (filter_var($protocolData['neighbor_address'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $IpVersion = 6;
        }

        if (strpos($protocolBody, 'hannel ipv' . $IpVersion) === 0) {
            foreach (explode("\n", 'C' . $protocolBody) as $protocolBodyLine) {
                if (strpos($protocolBodyLine, ':') !== false) {
                    $lineParts = explode(':', $protocolBodyLine, 2);
                    $protocolData[str_replace(' ', '_', strtolower(trim($lineParts[0])))] = trim($lineParts[1]);
                }
            }

            // Fix up the ROUTES
            if (isset($protocolData['routes'])) {
                $routeParts = explode(', ', $protocolData['routes']);
                unset($protocolData['routes']);
                foreach ($routeParts as $routePart) {
                    $routeDetail = explode(' ', $routePart);
                    $protocolData['routes'][$routeDetail[1]] = $routeDetail[0];
                }
            }

            // Set the route updates
            unset($protocolData['route_change_stats']);
            foreach (['import_updates', 'import_withdraws', 'export_updates', 'export_withdraws'] as $key) {
                if (! isset($protocolData[$key])) {
                    continue;
                }

                $routeChange_parts = preg_split("/\s+/", trim($protocolData[$key]));

                unset($protocolData[$key]);
                $protocolData['route_change_stats'][$key] = [
                    'received' => $routeChange_parts[0],
                    'rejected' => $routeChange_parts[1],
                    'filtered' => $routeChange_parts[2],
                    'ignored' => $routeChange_parts[3],
                    'accepted' => $routeChange_parts[4],
                ];
            }
        }
    }

    $protocolsData[] = $protocolData;
}

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
    $bgpPeer = BgpPeer::firstOrNew([
        'device_id' => $device['device_id'],
        'bgpPeerRemoteAs' => $protocol['neighbor_as'],
        'bgpLocalAddr' => $protocol['source_address'] ?: '0.0.0.0',
        'bgpPeerRemoteAddr' => $protocol['neighbor_address'],
    ]);

    $bgpPeer->device_id = $device['device_id'];
    $bgpPeer->astext = \LibreNMS\Util\AutonomousSystem::get($protocol['neighbor_as'])->name();
    $bgpPeer->bgpPeerIdentifier = $protocol['neighbor_id'] ?: '0.0.0.0';
    $bgpPeer->bgpPeerRemoteAs = $protocol['neighbor_as'];
    $bgpPeer->bgpPeerState = strtolower($protocol['bgp_state']);
    $bgpPeer->bgpPeerAdminStatus = str_replace('up', 'start', strtolower($protocol['protocol_state']));

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

        $bgpPeer->bgpPeerLastErrorText = $protocol['neighbor_id'] ?: '0.0.0.0';
    }

    $bgpPeer->bgpLocalAddr = $protocol['source_address'] ?: '0.0.0.0';
    $bgpPeer->bgpPeerRemoteAddr = $protocol['neighbor_address'];
    $bgpPeer->bgpPeerDescr = $protocol['description'] ?: $protocol['name'];
    $bgpPeer->bgpPeerInUpdates = intval($protocol['route_change_stats']['import_updates']['accepted']);
    $bgpPeer->bgpPeerOutUpdates = intval($protocol['route_change_stats']['export_updates']['accepted']);
    $bgpPeer->bgpPeerInTotalMessages = intval($protocol['route_change_stats']['import_updates']['received']);
    $bgpPeer->bgpPeerOutTotalMessages = intval($protocol['route_change_stats']['export_updates']['received']);

    $bgpPeer->bgpPeerFsmEstablishedTime = Carbon::parse($protocol['since'])->diffInSeconds(Carbon::now());
    $bgpPeer->bgpPeerInUpdateElapsedTime = Carbon::parse($protocol['since'])->diffInSeconds(Carbon::now());
    $bgpPeer->save();

    echo PHP_EOL . $name . ': Processed peer AS' . $bgpPeer->bgpPeerRemoteAs . ' (' . $bgpPeer->astext . ')';

    $bgpPeerIds[] = $bgpPeer->bgpPeer_id;
}

echo PHP_EOL;

// Clean up any bgpPeers that arent on the list for this device
BgpPeer::where('device_id', $device['device_id'])->whereNotIn('bgpPeer_id', $bgpPeerIds)->delete();
