<?php

if (starts_with($device['sysObjectID'], '.1.3.6.1.4.1.259.6.')) {              //ES3528M0
    $tmp_mib = 'ES3528MO-MIB';
} elseif (starts_with($device['sysObjectID'], '.1.3.6.1.4.1.259.10.1.22.')) {  //ES3528MV2
    $tmp_mib = 'ES3528MV2-MIB';
} elseif (starts_with($device['sysObjectID'], '.1.3.6.1.4.1.259.10.1.24.')) {  //ECS4510
    $tmp_mib = 'ECS4510-MIB';
} elseif (starts_with($device['sysObjectID'], '.1.3.6.1.4.1.259.10.1.39.')) {  //ECS4110
    $tmp_mib  = 'ECS4110-MIB';
} elseif (starts_with($device['sysObjectID'], '.1.3.6.1.4.1.259.10.1.42.')) { //ECS4210
    $tmp_mib = 'ECS4210-MIB';
} elseif (starts_with($device['sysObjectID'], '.1.3.6.1.4.1.259.10.1.27.')) {  //ECS3510
    $tmp_mib = 'ECS3510-MIB';
} elseif (starts_with($device['sysObjectID'], '.1.3.6.1.4.1.259.10.')) {       //ECS4120
    $tmp_mib = 'ECS4120-MIB';
} elseif (starts_with($device['sysObjectID'], '.1.3.6.1.4.1.259.8.1.11')) {    //ES3510MA
    $tmp_mib = 'ES3510MA-MIB';
}

$tmp_edgecos = snmp_get_multi($device, ['swOpCodeVer.1', 'swProdName.0', 'swSerialNumber.1', 'swHardwareVer.1'], '-OQUs', $tmp_mib);

$version  = trim($tmp_edgecos[1]['swHardwareVer'], '"') . ' ' . trim($tmp_edgecos[1]['swOpCodeVer'], '"');
$hardware = trim($tmp_edgecos[0]['swProdName'], '"');
$serial   = trim($tmp_edgecos[1]['swSerialNumber'], '"');

unset($temp_mibs, $tmp_edgecos);
