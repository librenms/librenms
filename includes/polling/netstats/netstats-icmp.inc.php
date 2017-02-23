<?php

use LibreNMS\RRD\RrdDefinition;

if (!starts_with($device['os'], array('Snom', 'asa'))) {
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
    $data = $data[0];

    if (isset($data['icmpInMsgs']) && isset($data['icmpOutMsgs'])) {
        $rrd_def = new RrdDefinition();
        $fields = array();
        foreach ($oids as $oid) {
            $rrd_def->addDataset($oid, 'COUNTER', null, 100000000000);
            $fields[$oid] = isset($data[$oid]) ? $data[$oid] : 'U';
        }

        $tags = compact('rrd_def');
        data_update($device, 'netstats-icmp', $tags, $fields);

        $graphs['netstat_icmp']      = true;
        $graphs['netstat_icmp_info'] = true;
    }

    unset($oids, $data, $rrd_def, $fields, $tags);
}//end if
