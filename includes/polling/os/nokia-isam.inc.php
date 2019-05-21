<?php

$oids = ['eqptHolderActualType.17', 'eqptHolderSerialNumber.17', 'swEtsiVersion.0'];
$snmp = snmp_get_multi($device, $oids, '-OQUs', 'ASAM-EQUIP-MIB:ASAM-SYSTEM-MIB');

$hardware = $snmp[17]['eqptHolderActualType'];
$serial = $snmp[17]['eqptHolderSerialNumber'];
$version = $snmp[0]['swEtsiVersion'];
