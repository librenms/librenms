<?php

// JUNIPER-WX-COMMON-MIB::jnxWxSysSwVersion.0 = STRING: 5.6.2.0
// JUNIPER-WX-COMMON-MIB::jnxWxSysHwVersion.0 = STRING: 1.0
// JUNIPER-WX-COMMON-MIB::jnxWxSysSerialNumber.0 = STRING: 0060000604
// JUNIPER-WX-COMMON-MIB::jnxWxChassisType.0 = INTEGER: jnxWx60(10)
$version = snmp_get($device, 'jnxWxSysSwVersion.0', '-Ovq', 'JUNIPER-WX-GLOBAL-REG');

$serial = snmp_get($device, 'jnxWxSysSerialNumber.0', '-Ovq', 'JUNIPER-WX-GLOBAL-REG');

$hardware = snmp_get($device, 'jnxWxChassisType.0', '-Ovq', 'JUNIPER-WX-GLOBAL-REG');
$hardware = strtoupper(str_replace('jnx', '', $hardware));
$hardware .= ' ' . snmp_get($device, 'jnxWxSysHwVersion.0', '-Ovq', 'JUNIPER-WX-GLOBAL-REG');
