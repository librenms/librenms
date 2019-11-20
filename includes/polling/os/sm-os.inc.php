<?php

$smos_data = snmp_get_multi_oid(
    $device,
    ['softwareEquipmentReleaseBench1.0','unitPartNumber.1'],
    '-OUQn',
    'SIAE-SOFT-MIB:SIAE-UNIT-MIB'
);
$version  = $smos_data['.1.3.6.1.4.1.3373.1103.7.2.0'];
$hardware = $smos_data['.1.3.6.1.4.1.3373.1103.6.2.1.11.1'];

unset($smos_data);
