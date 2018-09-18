<?php
$version = preg_replace('/[\r\n\"]+/', ' ', snmp_get($device, "swVersion.0", "-OQv", "TELESTE-LUMINATO-MIB"));
$hardware = "Teleste " . preg_replace('/[\r\n\"]+/', ' ', snmp_get($device, "deviceName.0", "-OQv", "TELESTE-LUMINATO-MIB"));
$serial = preg_replace('/[\r\n\"]+/', ' ', snmp_get($device, "hwSerialNumber.0", "-OQv", "TELESTE-LUMINATO-MIB"));
