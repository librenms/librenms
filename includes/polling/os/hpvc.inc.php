<?php

// Format of sysDescr is hardware model followed by version followed by build date
$tempstr = substr($device['sysDescr'], 0, strrpos($device['sysDescr'], ' '));
$version = trim(substr($tempstr, strrpos($tempstr, ' ')));
$hardware = trim(substr($tempstr, 0, strrpos($tempstr, ' ')));

// Serial number is in sysName after string "VCEX"
$serial = substr(snmp_get($device, 'sysName.0', '-OvQ', 'SNMPv2-MIB:HOST-RESOURCES-MIB:SNMP-FRAMEWORK-MIB'), 4);
