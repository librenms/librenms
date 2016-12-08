<?php

$version = trim(snmp_get($device, "vpnRouterFirmware.0", "-OQv", "VIPRINET-MIB"), '"');
$hardware = "VPN Router " . trim(snmp_get($device, "vpnRouterModel.0", "-OQv", "VIPRINET-MIB"), '"');
$hostname = trim(snmp_get($device, "vpnRouterName.0", "-OQv", "VIPRINET-MIB"), '"');
$serial = trim(snmp_get($device, "vpnRouterSerial.0", "-OQv", "VIPRINET-MIB"), '"');
