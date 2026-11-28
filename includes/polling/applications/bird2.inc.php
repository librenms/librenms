<?php

use App\Models\BgpPeer;
use App\Models\BgpPeerCbgp;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use LibreNMS\Data\Source\Bird2;
use LibreNMS\Data\Store\Rrd;
use LibreNMS\RRD\RrdDefinition;
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
$peerAfiKeys = [];

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
    // key on the address, not the router id, the routing page filters v4/v6 on it
    $bgpPeer->bgpPeerIdentifier = $protocol['neighbor_address'];
    $bgpPeer->bgpPeerRemoteAs = $protocol['neighbor_as'];
    $bgpPeer->bgpPeerState = strtolower((string) $protocol['bgp_state']);
    $bgpPeer->bgpPeerAdminStatus = str_replace('up', 'start', strtolower((string) $protocol['protocol_state']));

    if (isset($protocol['last_error'])) {
        // Find the subcode if its there and set it
        foreach (trans('bgp.error_subcodes') as $mainCode => $subCodes) {
            foreach ($subCodes as $subCode => $message) {
                if ($message == $protocol['last_error']) {
                    $bgpPeer->bgpPeerLastErrorCode = $mainCode;
                    $bgpPeer->bgpPeerLastErrorSubCode = $subCode;
                }
            }
        }

        $bgpPeer->bgpPeerLastErrorText = $protocol['last_error'];
    }

    $bgpPeer->bgpLocalAddr = $protocol['source_address'] ?? '0.0.0.0';
    $bgpPeer->bgpPeerRemoteAddr = $protocol['neighbor_address'];
    $bgpPeer->bgpPeerDescr = $protocol['description'] ?? $protocol['name'];
    $bgpPeer->bgpPeerInUpdates = intval($protocol['route_change_stats']['import_updates']['accepted'] ?? 0);
    $bgpPeer->bgpPeerOutUpdates = intval($protocol['route_change_stats']['export_updates']['accepted'] ?? 0);
    $bgpPeer->bgpPeerInTotalMessages = intval($protocol['route_change_stats']['import_updates']['received'] ?? 0);
    $bgpPeer->bgpPeerOutTotalMessages = intval($protocol['route_change_stats']['export_updates']['received'] ?? 0);

    $bgpPeer->bgpPeerFsmEstablishedTime = (int) Carbon::parse($protocol['since'])->diffInSeconds(Carbon::now(), true);
    $bgpPeer->bgpPeerInUpdateElapsedTime = (int) Carbon::parse($protocol['since'])->diffInSeconds(Carbon::now(), true);
    $bgpPeer->save();

    // write same graphs as bgp-peers
    app('Datastore')->put($device, 'bgp', [
        'bgpPeerIdentifier' => $bgpPeer->bgpPeerIdentifier,
        'rrd_name' => Rrd::safeName('bgp-' . $bgpPeer->bgpPeerIdentifier),
        'rrd_def' => RrdDefinition::make()
            ->addDataset('bgpPeerOutUpdates', 'COUNTER', null, 100000000000)
            ->addDataset('bgpPeerInUpdates', 'COUNTER', null, 100000000000)
            ->addDataset('bgpPeerOutTotal', 'COUNTER', null, 100000000000)
            ->addDataset('bgpPeerInTotal', 'COUNTER', null, 100000000000)
            ->addDataset('bgpPeerEstablished', 'GAUGE', 0),
    ], [
        'bgpPeerOutUpdates' => $bgpPeer->bgpPeerOutUpdates,
        'bgpPeerInUpdates' => $bgpPeer->bgpPeerInUpdates,
        'bgpPeerOutTotal' => $bgpPeer->bgpPeerOutTotalMessages,
        'bgpPeerInTotal' => $bgpPeer->bgpPeerInTotalMessages,
        'bgpPeerEstablished' => $bgpPeer->bgpPeerFsmEstablishedTime,
    ]);

    foreach ($protocol['channels'] ?? [] as $channel) {
        // no id column, match on the composite key
        $afiKey = [
            'device_id' => $device['device_id'],
            'bgpPeerIdentifier' => $bgpPeer->bgpPeerIdentifier,
            'afi' => $channel['afi'],
            'safi' => $channel['safi'],
        ];
        $existing = BgpPeerCbgp::where($afiKey)->first();

        // bird has no prefix limits, but these are NOT NULL
        $values = [
            'PrefixAdminLimit' => (int) ($existing->PrefixAdminLimit ?? 0),
            'PrefixThreshold' => (int) ($existing->PrefixThreshold ?? 0),
            'PrefixClearThreshold' => (int) ($existing->PrefixClearThreshold ?? 0),
        ];

        foreach (Bird2::channelPrefixCounters($channel) as $field => $value) {
            $previous = (int) ($existing->$field ?? 0);

            $values[$field] = $value;
            $values[$field . '_prev'] = $previous;
            $values[$field . '_delta'] = $value - $previous;
        }

        DB::table('bgpPeers_cbgp')->updateOrInsert($afiKey, $values);

        app('Datastore')->put($device, 'cbgp', [
            'bgpPeerIdentifier' => $bgpPeer->bgpPeerIdentifier,
            'afi' => $channel['afi'],
            'safi' => $channel['safi'],
            'rrd_name' => Rrd::safeName('cbgp-' . $bgpPeer->bgpPeerIdentifier . '.' . $channel['afi'] . '.' . $channel['safi']),
            'rrd_def' => array_reduce(
                Bird2::PREFIX_DATASETS,
                fn ($def, $ds) => $def->addDataset($ds, 'GAUGE', null, 100000000000),
                RrdDefinition::make()
            ),
        ], Bird2::channelPrefixCounters($channel));

        $peerAfiKeys[] = [$bgpPeer->bgpPeerIdentifier, $channel['afi'], $channel['safi']];
    }

    echo PHP_EOL . $name . ': Processed peer AS' . $bgpPeer->bgpPeerRemoteAs . ' (' . $bgpPeer->astext . ')';

    $bgpPeerIds[] = $bgpPeer->bgpPeer_id;
}

echo PHP_EOL;

// Clean up any bgpPeers that arent on the list for this device
BgpPeer::where('device_id', $device['device_id'])->whereNotIn('bgpPeer_id', $bgpPeerIds)->delete();

BgpPeerCbgp::where('device_id', $device['device_id'])
    ->where(function ($query) use ($peerAfiKeys): void {
        foreach ($peerAfiKeys as [$identifier, $afi, $safi]) {
            $query->whereNot(fn ($q) => $q->where('bgpPeerIdentifier', $identifier)->where('afi', $afi)->where('safi', $safi));
        }
    })
    ->delete();
