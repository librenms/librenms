<?php

//SNMPv2-SMI::enterprises.39145.12.1.0 = STRING: "ICT1500-48SW"
//SNMPv2-SMI::enterprises.39145.12.4.0 = STRING: "1.03"

$deviceInfo = snmp_get_multi($device, ['deviceHardware.0', 'deviceFirmware.0'], '-OQ', 'ICT-SINE-WAVE-INVERTER-MIB');
$hardware = trim($deviceInfo[0]['ICT-SINE-WAVE-INVERTER-MIB::deviceHardware'], '" ');
$version = 'v' . trim($deviceInfo[0]['ICT-SINE-WAVE-INVERTER-MIB::deviceFirmware'], '" ');

