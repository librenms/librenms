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

    unset($snmpstring, $fields, $snmpdata, $snmpdata_cmd, $rrd_create);
    $rrd_file = $config['rrd_dir'].'/'.$device['hostname'].'/'.safename('netstats-snmp.rrd');

    $rrd_create = $config['rrd_rra'];

    foreach ($oids as $oid) {
        $oid_ds          = truncate($oid, 19, '');
        $rrd_create .= " DS:$oid_ds:COUNTER:600:U:100000000000";
    }

    $data_array = snmpwalk_cache_oid($device, 'snmp', array(), 'SNMPv2-MIB');

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

    if (isset($data_array[0]['snmpInPkts']) && isset($data_array[0]['snmpOutPkts'])) {
        if (!file_exists($rrd_file)) {
            rrdtool_create($rrd_file, $rrd_create);
        }

        rrdtool_update($rrd_file, $fields);
        $graphs['netstat_snmp']     = true;
        $graphs['netstat_snmp_pkt'] = true;
    }

    unset($oids, $data, $data_array, $oid, $protos);
}//end if
