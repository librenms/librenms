<?php

use Illuminate\Support\Str;
use LibreNMS\RRD\RrdDefinition;

if (! Str::startsWith($device['os'], ['Snom', 'asa'])) {
    echo ' ICMP';

    // Below have more oids, and are in trees by themselves, so we can snmpwalk_cache_oid them
    $oids = [
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
    ];

    $data = snmpwalk_cache_oid($device, 'icmp', [], 'IP-MIB');
    $data = $data[0];

    if (isset($data['icmpInMsgs']) && isset($data['icmpOutMsgs'])) {
        $rrd_def = new RrdDefinition();
        $fields = [];
        foreach ($oids as $oid) {
            $rrd_def->addDataset($oid, 'COUNTER', null, 100000000000);
            $fields[substr($oid, 0, 19)] = isset($data[$oid]) ? $data[$oid] : 'U';
        }

        $tags = compact('rrd_def');
        data_update($device, 'netstats-icmp', $tags, $fields);

        $os->enableGraph('netstat_icmp');
        $os->enableGraph('netstat_icmp_info');
    }

    unset($oids, $data, $rrd_def, $fields, $tags);
}//end if
