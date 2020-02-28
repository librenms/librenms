<?php

$version = trim(snmp_get($device, 'currentFirmwareVersion.0', '-Oqv', 'BWS-MIB'), '"');
$serial = trim(snmp_get($device, 'systemSerialNumber.0', '-Oqv', 'BWS-MIB'), '"');
$version = substr($version, 0, strrpos($version, '('));
