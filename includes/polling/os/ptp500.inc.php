<?php

use LibreNMS\RRD\RrdDefinition;

$version = $device['sysDescr'];
$masterSlaveMode = ucfirst(snmp_get($device, 'masterSlaveMode.0', '-Oqv', "CAMBIUM-PTP500-V2-MIB"));
$hardware = 'PTP 500 '. $masterSlaveMode;
