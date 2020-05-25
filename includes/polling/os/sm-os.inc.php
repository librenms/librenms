<?php

$smos_data = snmp_get_multi_oid(
    $device,
    ['softwareEquipmentReleaseBench1.0','unitPartNumber.1', 'unitLabel.1', 'unitParentSerialNumber.1'],
    '-OUQs',
    'SIAE-SOFT-MIB:SIAE-UNIT-MIB'
);


$version  = $smos_data['softwareEquipmentReleaseBench1.0'];
$hardware = "{$smos_data['unitLabel.1']} ({$smos_data['unitPartNumber.1']})";
$serial = $smos_data['unitParentSerialNumber.1'];

unset($smos_data);
