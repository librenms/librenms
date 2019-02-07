<?php

$hardware = snmp_get($device, 'eqptHolderActualType.17', '-OQv', 'ASAM-EQUIP-MIB');
$serial = snmp_get($device, "eqptHolderSerialNumber.17", '-OQv', 'ASAM-EQUIP-MIB');
//$serial = snmp_get($device, "eqptAsamId.0", '-OQv', 'ASAM-EQUIP-MIB');
$version = snmp_get($device, 'swEtsiVersion.0', '-OQv', 'ASAM-SYSTEM-MIB');
