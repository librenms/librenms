<?php

// MG-SNMP-UPS-MIB::upsmgIdentFamilyName.0 = STRING: "PULSAR M"
// MG-SNMP-UPS-MIB::upsmgIdentModelName.0 = STRING: "2200"
// MG-SNMP-UPS-MIB::upsmgIdentSerialNumber.0 = STRING: "AQ1H01024"
$version = trim(snmp_get($device, 'xupsIdentSoftwareVersion.0', '-OQv', 'XUPS-MIB'), '" ');
$hardware  = trim(snmp_get($device, 'xupsIdentModel.0', '-OQv', 'XUPS-MIB'), '" ');
$features = trim(snmp_get($device, 'xupsAgentModel.0', '-OQv', 'XUPS-MIB'), '" ');
$features .= ' '.trim(snmp_get($device, 'xupsAgentSoftwareVersion.0', '-OQv', 'XUPS-MIB'), '" ');
$serial = trim(snmp_get($device, 'xupsIdentSerialNumber.0', '-OQv', 'XUPS-MIB'), '" ');
