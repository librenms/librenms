<?php

use LibreNMS\RRD\RrdDefinition;

$oids = 'acSysIdName.0 acSysVersionSoftware.0 acSysIdSerialNumber.0';
$data = snmp_get_multi($device, $oids, '-OQUs', 'AC-SYSTEM-MIB');

d_echo($data);

$hardware     = $data[0]['acSysIdName'];
$version      = $data[0]['acSysVersionSoftware'];
$serial       = $data[0]['acSysIdSerialNumber'];

$oids = array();
$oids[] = 'acPerfTel2IP';
$oids[] = 'acPerfIP2Tel';
$data =  array();

foreach ($oids as $oid) {
    $data = snmpwalk_cache_oid($device, $oid, $data, 'AcPerfH323SIPGateway');
}

//d_echo($data);

foreach ((array)($data[0]) as $key => $value) {
//    d_echo(" -> $key:= $value \n");

    $name = preg_replace('#acPerf#', '', $key);

    if (preg_match('#Calls$#', $key)) {
//       d_echo(" -> OK for ".$name." := ".$value." \n");
        $rrd_def = RrdDefinition::make()->addDataset($key, 'COUNTER', 0);

        $fields = array(
            $key => $value,
        );

        $tags = compact('rrd_def');
        data_update($device, $key, $tags, $fields);

        $graphs[$key] = true;
    }
}

