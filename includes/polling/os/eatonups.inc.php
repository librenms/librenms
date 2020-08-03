<?php

$hardware = trim(snmp_get($device, 'upsIdentModel.0', '-OQv', 'UPS-MIB'), '" ');

$version = trim(snmp_get($device, 'upsIdentAgentSoftwareVersion.0', '-OQv', 'UPS-MIB'), '" ');
$version += ' / '.trim(snmp_get($device, 'upsIdentUPSSoftwareVersion.0', '-OQv', 'UPS-MIB'), '" ');

$serial = trim(snmp_get($device, 'upsIdentName.0', '-OQv', 'UPS-MIB'), '" ');
