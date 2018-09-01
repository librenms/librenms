<?php

$hardware = snmp_get($device, ".1.3.6.1.4.1.17409.1.3.3.2.2.1.4.1", "-OQv");
$serial = snmp_get($device, ".1.3.6.1.4.1.17409.1.3.3.2.2.1.5.1", "-OQv");