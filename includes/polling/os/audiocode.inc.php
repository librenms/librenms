<?php

use LibreNMS\RRD\RrdDefinition;

$oids = 'acSysIdName.0 acSysVersionSoftware.0 acSysIdSerialNumber.0';
$data = snmp_get_multi($device, $oids, '-OQUs', 'AC-SYSTEM-MIB');

$hardware     = $data[0]['acSysIdName'];
$version      = $data[0]['acSysVersionSoftware'];
$serial       = $data[0]['acSysIdSerialNumber'];

$oids = array();
$oids[] = 'acPerfTel2IP';
$oids[] = 'acPerfIP2Tel';

foreach ($oids as $oid) {
    $data = snmpwalk_cache_oid($device, $oid, array(), 'AcPerfH323SIPGateway');
    $nameRRD = "audiocode_" . preg_replace('#acPerf#', '', $oid);
    $rrd_def = RrdDefinition::make();
    $fields = array();

    foreach ((array)($data[0]) as $key => $value) {
        if (preg_match('#Calls$#', $key)) {
            $nameVar = preg_replace('#'.$oid.'#', '', $key);
            $rrd_def->addDataset($nameVar, 'COUNTER', 0);
            $fields[$key] = $value;
        }
    }
    $tags = compact('rrd_def');
    data_update($device, $nameRRD, $tags, $fields);

    $graphs[$nameRRD] = true;
}


