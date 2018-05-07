<?php

// OACOMMON-MIB::netspireDeviceModelName
$hardware = snmp_get($device, "1.3.6.1.4.1.1732.2.1.41.0", "-OQv");

// OACOMMON-MIB::netSpireDeviceDeviceSerialNo
$serial = preg_replace('/[\r\n\"]+/', ' ', snmp_get($device, "1.3.6.1.4.1.1732.2.1.11.0", "-OQv"));
