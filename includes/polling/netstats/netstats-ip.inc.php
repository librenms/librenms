<?php

use LibreNMS\RRD\RrdDefinition;

if (!starts_with($device['os'], array('Snom', 'asa'))) {
    echo ' IP';

    // These are at the start of large trees that we don't want to walk the entirety of, so we snmp_get_multi them
    $oids = array(
        'IP-MIB::ipForwDatagrams.0',
        'IP-MIB::ipInDelivers.0',
        'IP-MIB::ipInReceives.0',
        'IP-MIB::ipOutRequests.0',
        'IP-MIB::ipInDiscards.0',
        'IP-MIB::ipOutDiscards.0',
        'IP-MIB::ipOutNoRoutes.0',
        'IP-MIB::ipReasmReqds.0',
        'IP-MIB::ipReasmOKs.0',
        'IP-MIB::ipReasmFails.0',
        'IP-MIB::ipFragOKs.0',
        'IP-MIB::ipFragFails.0',
        'IP-MIB::ipFragCreates.0',
        'IP-MIB::ipInUnknownProtos.0',
        'IP-MIB::ipInHdrErrors.0',
        'IP-MIB::ipInAddrErrors.0',
    );

    $rrd_def = new RrdDefinition();
    foreach ($oids as $oid) {
        $rrd_def->addDataset($oid, 'COUNTER', null, 100000000000);
    }

    $data = snmp_get_multi($device, $oids, '-OQUs', 'IP-MIB');

    $fields = array();
    foreach ($oids as $oid) {
        if (is_numeric($data[0][$oid])) {
            $value = $data[0][$oid];
        } else {
            $value = 'U';
        }
        $fields[$oid] = $value;
    }

    if (isset($data[0]['ipOutRequests']) && isset($data[0]['ipInReceives'])) {
        $tags = compact('rrd_def');
        data_update($device, 'netstats-ip', $tags, $fields);

        $graphs['netstat_ip']      = true;
        $graphs['netstat_ip_frag'] = true;
    }
}//end if

unset($oids, $data, $snmpstring, $rrd_def, $fields, $tags);
