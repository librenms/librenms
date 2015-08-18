<?php

if ($device['os'] != 'Snom') {
    echo ' IP-FORWARD';

    // Below have more oids, and are in trees by themselves, so we can snmpwalk_cache_oid them
    $oids = array('ipCidrRouteNumber');

    unset($snmpstring, $fields, $snmpdata, $snmpdata_cmd, $rrd_create);
    $rrd_file = $config['rrd_dir'].'/'.$device['hostname'].'/netstats-ip_forward.rrd';

    $rrd_create = $config['rrd_rra'];

    foreach ($oids as $oid) {
        $oid_ds          = truncate($oid, 19, '');
        $rrd_create .= " DS:$oid_ds:GAUGE:600:U:1000000";
        // Limit to 1MPPS?
        $snmpstring .= ' IP-FORWARD-MIB::'.$oid.'.0';
    }

    $data = snmp_get_multi($device, $snmpstring, '-OQUs', 'IP-FORWARD-MIB');

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

    if (isset($data[0]['ipCidrRouteNumber'])) {
        if (!file_exists($rrd_file)) {
            rrdtool_create($rrd_file, $rrd_create);
        }

        rrdtool_update($rrd_file, $fields);
        $graphs['netstat_ip_forward'] = true;
    }
}

unset($oids, $data, $data_array, $oid, $protos, $snmpstring);
