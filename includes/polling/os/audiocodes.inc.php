<?php

use LibreNMS\RRD\RrdDefinition;

$oids = ['acSysIdName.0', 'acSysVersionSoftware.0', 'acSysIdSerialNumber.0'];
$data = snmp_get_multi($device, $oids, '-OQUs', 'AC-SYSTEM-MIB');

$hardware     = $data[0]['acSysIdName'];
$version      = $data[0]['acSysVersionSoftware'];
$serial       = $data[0]['acSysIdSerialNumber'];
