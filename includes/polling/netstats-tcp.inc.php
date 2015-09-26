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
    unset($snmpstring, $fields, $snmpdata, $snmpdata_cmd, $rrd_create);
    $rrd_file = $config['rrd_dir'].'/'.$device['hostname'].'/netstats-tcp.rrd';

    $rrd_create = $config['rrd_rra'];

    foreach ($oids as $oid) {
        $oid_ds          = truncate($oid, 19, '');
        $rrd_create .= " DS:$oid_ds:COUNTER:600:U:10000000";
        // Limit to 10MPPS
        $snmpstring .= ' TCP-MIB::'.$oid.'.0';
    }

    $snmpstring .= ' tcpHCInSegs.0';
    $snmpstring .= ' tcpHCOutSegs.0';

    $data = snmp_get_multi($device, $snmpstring, '-OQUs', 'TCP-MIB');

    $fields = array();

    foreach ($oids as $oid) {
        if (is_numeric($data[0][$oid])) {
            $value = $data[0][$oid];
        }
        else {
            $value = 'U';
        }
        $fields[$oid] = $value;
    }

    unset($snmpstring);

    if (isset($data[0]['tcpInSegs']) && isset($data[0]['tcpOutSegs'])) {
        if (!file_exists($rrd_file)) {
            rrdtool_create($rrd_file, $rrd_create);
        }

        rrdtool_update($rrd_file, $fields);
        $graphs['netstat_tcp'] = true;
    }

    unset($oids, $data, $data_array, $oid, $protos);
}//end if
