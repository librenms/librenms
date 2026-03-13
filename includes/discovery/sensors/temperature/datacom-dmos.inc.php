<?php

if (($device['os'] ?? '') !== 'datacom-dmos') {
    return;
}

$ifDescr = SnmpQuery::cache()->walk('IF-MIB::ifDescr')->pluck();
$transceivers = SnmpQuery::cache()
    ->mibDir('datacom')
    ->mibs(['DMOS-TRANSCEIVER-MIB'])
    ->hideMib()
    ->walk('transceiverEntry')
    ->table(1);

foreach ($transceivers as $ifIndex => $entry) {
    if (! isset($entry['temperature']) || $entry['temperature'] === '') {
        continue;
    }

    $oid = '.1.3.6.1.4.1.3709.3.6.8.1.1.1.1.' . $ifIndex;
    $descr = ($ifDescr[$ifIndex] ?? ('ifIndex ' . $ifIndex)) . ' Transceiver Temperature';

    discover_sensor(
        null,
        'temperature',
        $device,
        $oid,
        (string) $ifIndex,
        'datacom-dmos-transceiver',
        $descr,
        100,
        1,
        null,
        null,
        null,
        null,
        (float) $entry['temperature'],
        'snmp',
        (string) $ifIndex,
        'port',
        null,
        'transceiver'
    );
}
