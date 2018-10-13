<?php

$version = snmp_get($device, ".1.3.6.1.4.1.18086.3080.1.3.0", "-OQv");
$get_hardware = explode(',', snmp_get($device, ".1.3.6.1.4.1.18086.3080.1.2.0", "-OQv"));
$hardware = "Vigintos " . $get_hardware[0];
$serial = snmp_get($device, "1.3.6.1.4.1.18086.3080.1.1.0", "-OQv");
