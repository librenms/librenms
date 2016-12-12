<?php

if (!starts_with($device['os'], array('Snom', 'asa'))) {
    echo ' TCP';

    $oids = array(
        'tcpActiveOpens',
        'tcpPassiveOpens',
        'tcpAttemptFails',
        'tcpEstabResets',
        'tcpCurrEstab',
        'tcpInSegs',
        'tcpOutSegs',
        'tcpRetransSegs',
        'tcpInErrs',
        'tcpOutRsts',
    );

    $rrd_def = array();
    $snmpstring = '';
    foreach ($oids as $oid) {
        $oid_ds      = substr($oid, 0, 19);
        $rrd_def[]   = " DS:$oid_ds:COUNTER:600:U:10000000"; // Limit to 10MPPS
        $snmpstring .= ' TCP-MIB::'.$oid.'.0';
    }

    $snmpstring .= ' tcpHCInSegs.0';
    $snmpstring .= ' tcpHCOutSegs.0';

    $data = snmp_get_multi($device, $snmpstring, '-OQUs', 'TCP-MIB');
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
