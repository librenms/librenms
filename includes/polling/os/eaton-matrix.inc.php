<?php

$hardware = trim(snmp_get($device, "matConName.0", "-OQv", "TELECOM-MIB"), '" ');
$version = trim(snmp_get($device, "matAgentSoftwareVerison.0", "-OQv", "TELECOM-MIB"), '" ');
$serial = trim(snmp_get($device, "matConSerialNum.0", "-OQv", "TELECOM-MIB"), '" ');
