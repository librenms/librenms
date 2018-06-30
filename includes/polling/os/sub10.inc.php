<?php

if (preg_match("/Sub10 Systems - ([\s\d\w]+)/", $device['sysDescr'], $hardware)) {
    $hardware = $hardware[1];
} else {
    $hardware = $device['sysDescr'];
}

$version = str_replace('"', '', snmp_get($device, 'sub10UnitLclFirmwareVersion.0', '-Osqnv', 'SUB10SYSTEMS-MIB'));
$serial = str_replace('"', '', snmp_get($device, 'sub10UnitLclHWSerialNumber.0', '-Osqnv', 'SUB10SYSTEMS-MIB'));
