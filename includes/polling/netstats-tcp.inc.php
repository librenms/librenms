<?php

if ($device['os'] != 'Snom') {
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
        $oid_ds      = truncate($oid, 19, '');
        $rrd_def[]   = " DS:$oid_ds:COUNTER:600:U:10000000"; // Limit to 10MPPS
        $snmpstring .= ' TCP-MIB::'.$oid.'.0';
    }

    $snmpstring .= ' tcpHCInSegs.0';
    $snmpstring .= ' tcpHCOutSegs.0';

    $data = snmp_get_multi($device, $snmpstring, '-OQUs', 'TCP-MIB');
    $fields = $data[0];

    // use HC Segs if we have them.
    if (isset($fields['tcpHCInSegs']) && !empty($fields['tcpHCInSegs'])) {
        $fields['tcpInSegs'] = $fields['tcpHCInSegs'];
        $fields['tcpOutSegs'] = $fields['tcpHCOutSegs'];
        unset($fields['tcpHCInSegs'], $fields['tcpHCOutSegs']);
    }

    if (isset($fields['tcpInSegs']) && isset($fields['tcpOutSegs'])) {

        $tags = compact('rrd_def');
        data_update($device,'netstats-tcp',$tags,$fields);

        $graphs['netstat_tcp'] = true;
    }

    unset($oids, $data, $fields, $oid, $protos, $snmpstring);
}//end if
