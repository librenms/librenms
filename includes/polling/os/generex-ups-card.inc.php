<?php


$hardware = trim(snmp_get($device, 'upsIdentManufacturer.0', '-OQv', 'UPS-MIB'), '"');
$hardware .= ' - ';
$hardware .= trim(snmp_get($device, 'upsIdentModel.0', '-OQv', 'UPS-MIB'), '"');
$version  = snmp_get($device, 'upsIdentAgentSoftwareVersion.0', '-OQv', 'UPS-MIB');
