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

    $data = snmpwalk_cache_oid($device, 'icmp', array(), 'IP-MIB');
    $fields = $data[0];

    if (isset($fields['icmpInMsgs']) && isset($fields['icmpOutMsgs'])) {
        $rrd_def = array();
        foreach ($oids as $oid) {
            $oid_ds    = truncate($oid, 19, '');
            $rrd_def[] = "DS:$oid_ds:COUNTER:600:U:100000000000";
        }

        $tags = compact('rrd_def');
        data_update($device,'netstats-icmp',$tags,$fields);

        $graphs['netstat_icmp']      = true;
        $graphs['netstat_icmp_info'] = true;
    }

    unset($oids, $data, $rrd_def, $fields, $tags);
}//end if
