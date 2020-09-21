<?php

use Illuminate\Support\Str;
use LibreNMS\RRD\RrdDefinition;

if (! Str::startsWith($device['os'], ['Snom', 'asa'])) {
    echo ' UDP';

    $oids = [
        'udpInDatagrams',
        'udpOutDatagrams',
        'udpInErrors',
        'udpNoPorts',
    ];
    $data = snmp_getnext_multi($device, $oids, '-OQUs', 'UDP-MIB');

    if (is_numeric($data['udpInDatagrams']) && is_numeric($data['udpOutDatagrams'])) {
        $rrd_def = new RrdDefinition();
        $fields = [];
        foreach ($oids as $oid) {
            $rrd_def->addDataset($oid, 'COUNTER', null, 1000000); // Limit to 1MPPS?
            $fields[$oid] = is_numeric($data[$oid]) ? $data[$oid] : 'U';
        }

        $tags = compact('rrd_def');
        data_update($device, 'netstats-udp', $tags, $fields);

        $os->enableGraph('netstat_udp');

        unset($rrd_def, $fields, $tags, $oid);
    }

    unset($oids, $data);
}//end if
