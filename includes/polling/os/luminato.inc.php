<?php
$version = snmp_get($device, "1.3.6.1.2.1.47.1.1.1.1.10.10", "-OQv");
$hardware = snmp_get($device, "1.3.6.1.2.1.1.5.0", "-OQv");
$serial = snmp_get($device, "1.3.6.1.4.1.3715.99.2.1.2.1.7.10", "-OQv"); //Chassis Serial Number
