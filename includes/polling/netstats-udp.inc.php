<?php

if ($device['os'] != 'Snom') {
    echo ' UDP';

    // These are at the start of large trees that we don't want to walk the entirety of, so we snmpget_multi them
    $oids = array(
        'udpInDatagrams',
        'udpOutDatagrams',
        'udpInErrors',
        'udpNoPorts',
    );

    unset($snmpstring, $fields, $snmpdata, $snmpdata_cmd, $rrd_create);
    $rrd_file = $config['rrd_dir'].'/'.$device['hostname'].'/netstats-udp.rrd';

    $rrd_create = $config['rrd_rra'];

    foreach ($oids as $oid) {
        $oid_ds      = truncate($oid, 19, '');
        $rrd_create .= " DS:$oid_ds:COUNTER:600:U:1000000";
        // Limit to 1MPPS?
        $snmpstring .= ' UDP-MIB::'.$oid.'.0';
    }

    $data = snmp_get_multi($device, $snmpstring, '-OQUs', 'UDP-MIB');

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

    if (isset($data[0]['udpInDatagrams']) && isset($data[0]['udpOutDatagrams'])) {
        if (!file_exists($rrd_file)) {
            rrdtool_create($rrd_file, $rrd_create);
        }

        rrdtool_update($rrd_file, $fields);
        $graphs['netstat_udp'] = true;
    }
}//end if

unset($oids, $data, $data_array, $oid, $protos, $snmpstring);
