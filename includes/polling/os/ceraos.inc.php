<?php

$hardware = snmp_walk($device, 'MWRM-UNIT-MIB::genEquipInventoryCardName', '-Osqnv');
$serial = snmp_walk($device, 'MWRM-UNIT-MIB::genEquipInventorySerialNumber', '-Osqnv');
$version = snmp_walk($device, 'MWRM-UNIT-MIB::genEquipMngSwIDUVersionsRunningVersion.1', '-Osqnv');
