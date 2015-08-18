<?php

if ($device['os'] != 'Snom') {
    echo ' ICMP';

    // Below have more oids, and are in trees by themselves, so we can snmpwalk_cache_oid them
    $oids = array(
        'icmpInMsgs',
        'icmpOutMsgs',
        'icmpInErrors',
        'icmpOutErrors',
        'icmpInEchos',
        'icmpOutEchos',
        'icmpInEchoReps',
        'icmpOutEchoReps',
        'icmpInDestUnreachs',
        'icmpOutDestUnreachs',
        'icmpInParmProbs',
        'icmpInTimeExcds',
        'icmpInSrcQuenchs',
        'icmpInRedirects',
        'icmpInTimestamps',
        'icmpInTimestampReps',
        'icmpInAddrMasks',
        'icmpInAddrMaskReps',
        'icmpOutTimeExcds',
        'icmpOutParmProbs',
        'icmpOutSrcQuenchs',
        'icmpOutRedirects',
        'icmpOutTimestamps',
        'icmpOutTimestampReps',
        'icmpOutAddrMasks',
        'icmpOutAddrMaskReps',
    );

    unset($snmpstring, $fields, $snmpdata, $snmpdata_cmd, $rrd_create);
    $rrd_file = $config['rrd_dir'].'/'.$device['hostname'].'/netstats-icmp.rrd';

    $rrd_create = $config['rrd_rra'];

    foreach ($oids as $oid) {
        $oid_ds      = truncate($oid, 19, '');
        $rrd_create .= " DS:$oid_ds:COUNTER:600:U:100000000000";
        $snmpstring .= " $oid.0";
    }

    $data_array = snmpwalk_cache_oid($device, 'icmp', array(), 'IP-MIB');

    $fields = array();

    foreach ($oids as $oid) {
        if (is_numeric($data_array[0][$oid])) {
            $value = $data_array[0][$oid];
        }
        else {
            $value = 'U';
        }
        $fields[$oid] = $value;
    }

    unset($snmpstring);

    if (isset($data_array[0]['icmpInMsgs']) && isset($data_array[0]['icmpOutMsgs'])) {
        if (!file_exists($rrd_file)) {
            rrdtool_create($rrd_file, $rrd_create);
        }

        rrdtool_update($rrd_file, $fields);
        $graphs['netstat_icmp']      = true;
        $graphs['netstat_icmp_info'] = true;
    }

    unset($oids, $data, $data_array, $oid, $protos);
}//end if
