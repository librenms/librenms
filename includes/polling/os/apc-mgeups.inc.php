<?php

// MG-SNMP-UPS-MIB::upsmgIdentFamilyName.0 = STRING: "PULSAR M"
// MG-SNMP-UPS-MIB::upsmgIdentModelName.0 = STRING: "2200"
// MG-SNMP-UPS-MIB::upsmgIdentSerialNumber.0 = STRING: "AQ1H01024"
$version = trim(snmp_get($device, 'upsmgIdentFirmwareVersion.0', '-OQv', 'MG-SNMP-UPS-MIB'), '" ');

$hardware  = trim(snmp_get($device, 'upsmgIdentFamilyName.0', '-OQv', 'MG-SNMP-UPS-MIB'), '" ');
$hardware .= ' '.trim(snmp_get($device, 'upsmgIdentModelName.0', '-OQv', 'MG-SNMP-UPS-MIB'), '" ');

$serial = trim(snmp_get($device, 'upsmgIdentSerialNumber.0', '-OQv', 'MG-SNMP-UPS-MIB'), '" ');
