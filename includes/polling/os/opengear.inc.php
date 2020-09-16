<?php

// SNMPv2-SMI::enterprises.25049.17.1.1 = STRING: "3.16.6 058e5600 ()" - ogFirmwareVersion
// SNMPv2-SMI::enterprises.25049.17.1.2 = STRING: "7006851296650" - ogSerialNumber

$ogFirmwareVersion = snmp_get($device, '1.3.6.1.4.1.25049.17.1.1', '-OQv');
$ogFirmwareVersion = explode(' ', $ogFirmwareVersion);
$version = trim($ogFirmwareVersion[0], '" ');

$hardware = snmp_get($device, '1.3.6.1.2.1.1.2.0', '-OQv', "OG-PRODUCTS-MIB");
if (preg_match('/OG-PRODUCTS-MIB::og(.*)/', $hardware, $matches)) {
    $hardware = $matches[1];
}

$serial = trim(snmp_get($device, '1.3.6.1.4.1.25049.17.1.2', '-OQv'), '" ');
