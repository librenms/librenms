<?php
echo 'Scanning Avaya IPO...';

// Get OS version details
$version = snmp_get($device, 'SNMPv2-MIB::sysDescr.0', '-Oqvn');

// Get hardware details
$hardware = snmp_get($device, 'ENTITY-MIB::entPhysicalDescr.1', '-Oqvn');
