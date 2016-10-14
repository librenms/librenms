<?php

// NS-ROOT-MIB::sysHardwareVersionDesc.0 = STRING: "7000 v1 6*EZ+2*EM"
// NS-ROOT-MIB::sysBuildVersion.0 = STRING: "NetScaler NS8.1: Build 69.4, Date: Jan 28 2010, 02:00:43  "
$hardware = str_replace('"', '', snmp_get($device, 'sysHardwareVersionDesc.0', '-Osqv', 'SNMPv2-MIB:NS-ROOT-MIB'));
$version  = str_replace('"', '', snmp_get($device, 'sysBuildVersion.0', '-Osqv', 'SNMPv2-MIB:NS-ROOT-MIB'));
$serial   = str_replace('"', '', snmp_get($device, 'sysHardwareSerialNumber.0', '-Osqv', 'SNMPv2-MIB:NS-ROOT-MIB'));

list($version,$features) = explode(':', $version);
list(,$version)          = explode(' ', $version);
list($features)          = explode(',', trim($features));

require 'includes/polling/os/misc/netscaler-stats.inc.php';
