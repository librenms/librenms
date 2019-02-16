<?php
$osVersion  = snmp_get($device, 'sdWANStatsApplianceOSVersion', '-Osqv', 'CITRIX-NetScaler-SD-WAN-MIB');
$swVersion  = snmp_get($device, 'sdWANStatsApplianceSoftwareVersion', '-Osqv', 'CITRIX-NetScaler-SD-WAN-MIB');
$version    = "OS: $osVersion, SW: $swVersion";

$serial     = snmp_get($device, 'sdWANStatsApplianceSerialNumber', '-Osqv', 'CITRIX-NetScaler-SD-WAN-MIB');

// Appears to be the model name from the MIB, to be checked in real
$hardware   = snmp_get($device, 'sdWANStatsApplianceName', '-Osqv', 'CITRIX-NetScaler-SD-WAN-MIB');
