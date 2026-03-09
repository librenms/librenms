<?php

if (($device['os'] ?? '') !== 'datacom-dmos' || stripos((string) ($device['hardware'] ?? ''), 'DM4370') === false) {
    return;
}

$ifDescr = SnmpQuery::cache()->walk('IF-MIB::ifDescr')->pluck();
$laneTable = SnmpQuery::cache()
    ->mibDir('datacom')
    ->mibs(['DMOS-TRANSCEIVER-MIB'])
    ->hideMib()
    ->walk('transceiverLaneEntry')
    ->table(2);

$lanes = [];
foreach ($laneTable as $ifIndex => $rows) {
    if (isset($rows['laneIndex']) || isset($rows['laneTxBias'])) {
        $lane = $rows['laneIndex'] ?? '0';
        $lanes[] = [(string) $ifIndex, (string) $lane, $rows];
        continue;
    }

    if (! is_array($rows)) {
        continue;
    }

    foreach ($rows as $laneIndex => $entry) {
        if (! is_array($entry)) {
            continue;
        }

        $lane = $entry['laneIndex'] ?? (string) $laneIndex;
        $lanes[] = [(string) $ifIndex, (string) $lane, $entry];
    }
}

foreach ($lanes as [$ifIndex, $lane, $entry]) {
    if (! isset($entry['laneTxBias']) || $entry['laneTxBias'] === '') {
        continue;
    }

    $ifName = $ifDescr[$ifIndex] ?? ('ifIndex ' . $ifIndex);
    $oid = '.1.3.6.1.4.1.3709.3.6.8.2.1.1.4.' . $ifIndex . '.' . $lane;

    discover_sensor(
        null,
        'current',
        $device,
        $oid,
        $ifIndex . '.' . $lane . '.txbias',
        'datacom-dmos-transceiver',
        $ifName . ' Lane ' . $lane . ' Tx Bias',
        100,
        1,
        null,
        null,
        null,
        null,
        (float) $entry['laneTxBias'],
        'snmp',
        (string) $ifIndex,
        'port',
        null,
        'transceiver'
    );
}
