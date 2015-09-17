<?php

echo 'Scanning Avaya IP Office...';

$sysObjectID = snmp_get($device, 'sysObjectID.0', '-Oqvn');

$version = snmp_get($device, 'SNMPv2-MIB::sysDescr.0', '-Oqvn');
$hardware = snmp_get($device, 'ENTITY-MIB::entPhysicalDescr.1', '-Oqvn');
