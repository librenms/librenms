<?php

// .1.3.6.1.2.1.33.1.1.2.0 = STRING: "TRIPP LITE PDUMH20HVATNET"
// .1.3.6.1.2.1.33.1.1.4.0 = STRING: "12.04.0052"
// .1.3.6.1.2.1.33.1.1.5.0 = STRING: "sysname.company.com"
// .1.3.6.1.4.1.850.100.1.1.4.0 = STRING: "9942AY0AC796000912"
// .1.3.6.1.4.1.850.10.2.2.1.12.1 = STRING: "This Is My Location"
$sysName     = snmp_get($device, 'UPS-MIB::upsIdentName.0', '-Ovq');
$hardware    = str_replace('TRIPP LITE ', '', snmp_get($device, 'UPS-MIB::upsIdentModel.0', '-Ovq'));
$version     = snmp_get($device, 'UPS-MIB::upsIdentAgentSoftwareVersion.0', '-Ovq');
$sysLocation = trim(snmp_get($device, '.1.3.6.1.4.1.850.10.2.2.1.12.1', '-Ovq', 'TRIPPLITE-MIB'), '"');
$serial      = trim(snmp_get($device, '.1.3.6.1.4.1.850.100.1.1.4.0', '-Ovq', 'TRIPPLITE-MIB'), '"');
