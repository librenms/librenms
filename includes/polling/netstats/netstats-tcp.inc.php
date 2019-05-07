<?php

use LibreNMS\RRD\RrdDefinition;

if (!starts_with($device['os'], array('Snom', 'asa'))) {
    echo ' TCP';

    $oids = array(
        'TCP-MIB::tcpActiveOpens.0',
        'TCP-MIB::tcpPassiveOpens.0',
        'TCP-MIB::tcpAttemptFails.0',
        'TCP-MIB::tcpEstabResets.0',
        'TCP-MIB::tcpCurrEstab.0',
        'TCP-MIB::tcpInSegs.0',
        'TCP-MIB::tcpOutSegs.0',
        'TCP-MIB::tcpRetransSegs.0',
        'TCP-MIB::tcpInErrs.0',
        'TCP-MIB::tcpOutRsts.0',
    );

    $rrd_def = new RrdDefinition();
    foreach ($oids as $oid) {
        $rrd_def->addDataset($oid, 'COUNTER', null, 10000000);
    }

    array_push($oids, 'TCP-MIB::tcpHCInSegs.0', 'TCP-MIB::tcpHCOutSegs.0');

    $data = snmp_get_multi($device, $oids, '-OQUs', 'TCP-MIB');
    $data = $data[0];

    if (isset($data['tcpInSegs']) && isset($data['tcpOutSegs'])) {
        $fields = array();
        foreach ($oids as $oid) {
            $fields[$oid] = isset($data[$oid]) ? $data[$oid] : 'U';
        }

        // use HC Segs if we have them.
        if (isset($data['tcpHCInSegs'])) {
            if (!empty($data['tcpHCInSegs'])) {
                $fields['tcpInSegs'] = $data['tcpHCInSegs'];
                $fields['tcpOutSegs'] = $data['tcpHCOutSegs'];
            }
        }

        $tags = compact('rrd_def');
        data_update($device, 'netstats-tcp', $tags, $fields);

        $graphs['netstat_tcp'] = true;
    }

    unset($oids, $data, $fields, $oid, $snmpstring);
}//end if
