<?php
$osVersion = str_replace('"', '', snmp_get($device, 'sdWANStatsApplianceOSVersion', '-Osqv', 'CITRIX-NetScaler-SD-WAN-MIB'));
$swVersion  = str_replace('"', '', snmp_get($device, 'sdWANStatsApplianceSoftwareVersion', '-Osqv', 'CITRIX-NetScaler-SD-WAN-MIB'));
$version = "OS: $osVersion, SW: $swVersion";
$serial   = str_replace('"', '', snmp_get($device, 'sdWANStatsApplianceSerialNumber', '-Osqv', 'CITRIX-NetScaler-SD-WAN-MIB'));
