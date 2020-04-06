<?php

//SNMPv2-SMI::enterprises.39145.13.1.0 = STRING: "Modular Power Series 48V"
//SNMPv2-SMI::enterprises.39145.13.4.0 = STRING: "1.04"

$deviceInfo = snmp_get_multi($device, ['deviceHardware.0', 'deviceFirmware.0'], '-OQ', 'ICT-MODULAR-POWER-SYSTEM-MIB');
$hardware = trim($deviceInfo[0]['ICT-MODULAR-POWER-SYSTEM-MIB::deviceHardware'], '" ');
$version = 'v' . trim($deviceInfo[0]['ICT-MODULAR-POWER-SYSTEM-MIB::deviceFirmware'], '" ');

