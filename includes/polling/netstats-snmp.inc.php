<?php

if ($device['os'] != 'Snom') {
    echo ' SNMP';

    // Below have more oids, and are in trees by themselves, so we can snmpwalk_cache_oid them
    $oids = array(
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
    );

    $data = snmpwalk_cache_oid($device, 'snmp', array(), 'SNMPv2-MIB');

    $fields = $data[0];
    unset($fields['snmpEnableAuthenTraps']);

    if (isset($fields['snmpInPkts']) && isset($fields['snmpOutPkts'])) {
        $rrd_def = array();
        foreach ($oids as $oid) {
            $oid_ds    = truncate($oid, 19, '');
            $rrd_def[] = "DS:$oid_ds:COUNTER:600:U:100000000000";
        }

        $tags = compact('rrd_def');
        data_update($device,'netstats-snmp',$tags,$fields);

        $graphs['netstat_snmp']     = true;
        $graphs['netstat_snmp_pkt'] = true;
    }

    unset($oids, $data, $feilds, $oid, $rrd_def, $tags);
}//end if
