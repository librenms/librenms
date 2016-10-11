<?php

$hardware = snmp_get($device, 'genEquipInventoryCardName.127', '-Osqnv', 'MWRM-UNIT-MIB');
$serial = snmp_get($device, 'genEquipInventorySerialNumber.127', '-Osqnv', 'MWRM-UNIT-MIB');
$version = snmp_get($device, 'genEquipMngSwIDUVersionsRunningVersion.1', '-Osqnv', 'MWRM-UNIT-MIB');
