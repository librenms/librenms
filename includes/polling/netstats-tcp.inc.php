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

    // $oids['tcp_collect'] = $oids['tcp'];
    // $oids['tcp_collect'][] = 'tcpHCInSegs';
    // $oids['tcp_collect'][] = 'tcpHCOutSegs';

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

    unset($snmpstring);

    if (isset($fields['tcpInSegs']) && isset($fields['tcpOutSegs'])) {

        $tags = compact('rrd_def');
        data_update($device,'netstats-tcp',$tags,$fields);

        $graphs['netstat_tcp'] = true;
    }

    unset($oids, $data, $data, $oid, $protos);
}//end if
