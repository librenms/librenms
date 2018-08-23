<?php

$version = snmp_get($device, "1.3.6.1.2.1.1.1.0", "-OQv");
$get_hardware = explode(',', snmp_get($device, "1.3.6.1.2.1.1.5.0", "-OQv"));
$hardware = "Teleste" . $get_hardware[0];
#Chassis Serial Number
$serial = snmp_get($device, "1.3.6.1.4.1.3715.99.2.1.2.1.7.10", "-OQv");
