<?php

$get_hardware = explode(',', snmp_get($device, "1.3.6.1.2.1.47.1.1.1.1.2.888624", "-OQv"));
$hardware = "SmartAX " . $get_hardware[0];
$version = snmp_get($device, "1.3.6.1.4.1.2011.6.3.1.999.0", "-OQv");
$serial = snmp_get($device, "1.3.6.1.4.1.2011.6.3.3.1.1.9.0", "-OQv");
