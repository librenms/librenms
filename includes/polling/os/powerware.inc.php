<?php

// XUPS-MIB::xupsIdentManufacturer.0 = STRING: "Powerware Corporation"
// XUPS-MIB::xupsIdentModel.0 = STRING: "T1500 XR  "
$hardware = trim(snmp_get($device, 'xupsIdentManufacturer.0', '-OQv', 'XUPS-MIB'), '" ');
$hardware .= ' ' . trim(snmp_get($device, 'xupsIdentModel.0', '-OQv', 'XUPS-MIB'), '" ');

// XUPS-MIB::xupsIdentSoftwareVersion.0 = STRING: " FP:  2.01  INV:  2.01  NET: 3.60 "
$version = trim(snmp_get($device, 'xupsIdentSoftwareVersion.0', '-OQv', 'XUPS-MIB'), '" ');
