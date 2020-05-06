<?php

$deviceInfo = snmp_get_multi($device, ['deviceHardware.0', 'deviceFirmware.0'], '-OQ', 'ICT-MODULAR-POWER-SYSTEM-MIB');
$hardware = trim($deviceInfo[0]['ICT-MODULAR-POWER-SYSTEM-MIB::deviceHardware'], '" ');
$version = 'v' . trim($deviceInfo[0]['ICT-MODULAR-POWER-SYSTEM-MIB::deviceFirmware'], '" ');
