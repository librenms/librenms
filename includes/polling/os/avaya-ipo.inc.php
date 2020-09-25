<?php

echo 'Scanning Avaya IP Office...';

$physDevice = snmp_get($device, 'ENTITY-MIB::entPhysicalDescr.1', '-Oqvn');

if (strstr($physDevice, 'Avaya IP Office')) {
    $hardware = $physDevice;
}

$version = $device['sysDescr'];
