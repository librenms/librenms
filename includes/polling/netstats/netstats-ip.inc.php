<?php

use LibreNMS\RRD\RrdDefinition;

if (!starts_with($device['os'], ['Snom', 'asa'])) {
    echo ' IP';

    // These are at the start of large trees that we don't want to walk the entirety of, so we snmp_get_multi them
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

    $rrd_def = new RrdDefinition();
    $fields = [];
    foreach ($oids as $oid) {
        $rrd_def->addDataset($oid, 'COUNTER', null, 100000000000);
        $fields[$oid] = is_numeric($data[$oid]) ? $data[$oid] : 'U';
    }

    if (isset($data['ipOutRequests']) && isset($data['ipInReceives'])) {
        $tags = compact('rrd_def');
        data_update($device, 'netstats-ip', $tags, $fields);

        $graphs['netstat_ip']      = true;
        $graphs['netstat_ip_frag'] = true;
    }
}//end if

unset($oids, $data, $rrd_def, $fields, $tags);
