<?php

use LibreNMS\RRD\RrdDefinition;

$hardware = snmp_get($device, 'entPhysicalModelName.2', '-Osqnv', 'ENTITY-MIB');
$serial = snmp_get($device, 'entPhysicalSerialNum.2', '-Osqnv', 'ENTITY-MIB');
$version = snmp_get($device, 'entPhysicalSoftwareRev.2', '-Osqnv', 'ENTITY-MIB');
