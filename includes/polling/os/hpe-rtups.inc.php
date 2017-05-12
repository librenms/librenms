<?php

$version = trim(snmp_get($device, 'upsmgIdentFirmwareVersion.0', '-OQv', 'MG-SNMP-UPS-MIB'), '" ');

$hardware  = trim(snmp_get($device, 'upsmgIdentFamilyName.0', '-OQv', 'MG-SNMP-UPS-MIB'), '" ');
$hardware .= ' '.trim(snmp_get($device, 'upsmgIdentModelName.0', '-OQv', 'MG-SNMP-UPS-MIB'), '" ');

$serial = trim(snmp_get($device, 'upsmgIdentSerialNumber.0', '-OQv', 'MG-SNMP-UPS-MIB'), '" ');
