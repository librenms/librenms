<?php

use Illuminate\Support\Str;
use LibreNMS\RRD\RrdDefinition;

if (! Str::startsWith($device['os'], ['Snom', 'asa'])) {
    echo ' IP';

    $oids = [
        'ipForwDatagrams',
        'ipInDelivers',
        'ipInReceives',
        'ipOutRequests',
        'ipInDiscards',
        'ipOutDiscards',
        'ipOutNoRoutes',
        'ipReasmReqds',
        'ipReasmOKs',
        'ipReasmFails',
        'ipFragOKs',
        'ipFragFails',
        'ipFragCreates',
        'ipInUnknownProtos',
        'ipInHdrErrors',
        'ipInAddrErrors',
    ];
    $data = snmp_getnext_multi($device, $oids, '-OQUs', 'IP-MIB');

    if (is_numeric($data['ipOutRequests']) && is_numeric($data['ipInReceives'])) {
        $rrd_def = new RrdDefinition();
        $fields = [];
        foreach ($oids as $oid) {
            $rrd_def->addDataset($oid, 'COUNTER', null, 100000000000);
            $fields[$oid] = is_numeric($data[$oid]) ? $data[$oid] : 'U';
        }

        $tags = compact('rrd_def');
        data_update($device, 'netstats-ip', $tags, $fields);

        $os->enableGraph('netstat_ip');
        $os->enableGraph('netstat_ip_frag');

        unset($rrd_def, $fields, $tags, $oid);
    }

    unset($oids, $data);
}//end if
