<?php

if ($device['os'] != 'Snom') {
    echo ' IP';

    // These are at the start of large trees that we don't want to walk the entirety of, so we snmp_get_multi them
    $oids = array(
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
    );

    unset($snmpstring, $fields, $snmpdata, $snmpdata_cmd, $rrd_create);
    $rrd_file = $config['rrd_dir'].'/'.$device['hostname'].'/netstats-ip.rrd';

    $rrd_create = $config['rrd_rra'];

    foreach ($oids as $oid) {
        $oid_ds          = truncate($oid, 19, '');
        $rrd_create .= " DS:$oid_ds:COUNTER:600:U:100000000000";
        $snmpstring .= ' IP-MIB::'.$oid.'.0';
    }

    $data = snmp_get_multi($device, $snmpstring, '-OQUs', 'IP-MIB');

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

    if (isset($data[0]['ipOutRequests']) && isset($data[0]['ipInReceives'])) {
        if (!file_exists($rrd_file)) {
            rrdtool_create($rrd_file, $rrd_create);
        }

        rrdtool_update($rrd_file, $fields);

        $tags = array();
        influx_update($device,'netstats-ip',$tags,$fields);

        $graphs['netstat_ip']      = true;
        $graphs['netstat_ip_frag'] = true;
    }
}//end if

unset($oids, $data, $data_array, $oid);
