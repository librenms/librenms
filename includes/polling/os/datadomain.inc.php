<?php

//iso.3.6.1.2.1.1.1.0 = STRING: "Data Domain OS 5.7.0.4-513368"

preg_match('/Data Domain OS (.*)/', $device['sysDescr'], $matches);
$version  = $matches[1];
$hardware = trim(snmp_get($device, "systemHardwareDevice", "-OQv", "DATA-DOMAIN-MIB"), '"');
