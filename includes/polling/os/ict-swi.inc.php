<?php

$deviceInfo = snmp_get_multi($device, ['deviceHardware.0', 'deviceFirmware.0'], '-OQ', 'ICT-SINE-WAVE-INVERTER-MIB');
$hardware = trim($deviceInfo[0]['ICT-SINE-WAVE-INVERTER-MIB::deviceHardware'], '" ');
$version = 'v' . trim($deviceInfo[0]['ICT-SINE-WAVE-INVERTER-MIB::deviceFirmware'], '" ');
