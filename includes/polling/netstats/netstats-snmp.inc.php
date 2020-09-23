<?php

use LibreNMS\RRD\RrdDefinition;

if ($device['os'] != 'Snom') {
    echo ' SNMP';

    // Below have more oids, and are in trees by themselves, so we can snmpwalk_cache_oid them
    $oids = [
        'snmpInPkts',
        'snmpOutPkts',
        'snmpInBadVersions',
        'snmpInBadCommunityNames',
        'snmpInBadCommunityUses',
        'snmpInASNParseErrs',
        'snmpInTooBigs',
        'snmpInNoSuchNames',
        'snmpInBadValues',
        'snmpInReadOnlys',
        'snmpInGenErrs',
        'snmpInTotalReqVars',
        'snmpInTotalSetVars',
        'snmpInGetRequests',
        'snmpInGetNexts',
        'snmpInSetRequests',
        'snmpInGetResponses',
        'snmpInTraps',
        'snmpOutTooBigs',
        'snmpOutNoSuchNames',
        'snmpOutBadValues',
        'snmpOutGenErrs',
        'snmpOutGetRequests',
        'snmpOutGetNexts',
        'snmpOutSetRequests',
        'snmpOutGetResponses',
        'snmpOutTraps',
        'snmpSilentDrops',
        'snmpProxyDrops',
    ];

    $data = snmpwalk_cache_oid($device, 'snmp', [], 'SNMPv2-MIB');

    if (isset($data[0]['snmpInPkts'])) {
        $rrd_def = new RrdDefinition();
        $fields = [];
        foreach ($oids as $oid) {
            $rrd_def->addDataset($oid, 'COUNTER', null, 100000000000);
            $fields[substr($oid, 0, 19)] = isset($data[0][$oid]) ? $data[0][$oid] : 'U';
        }

        $tags = compact('rrd_def');
        data_update($device, 'netstats-snmp', $tags, $fields);

        $os->enableGraph('netstat_snmp');
        $os->enableGraph('netstat_snmp_pkt');
    }

    unset($oids, $data, $rrd_def, $fields, $tags);
}//end if
