<?php

$serial = trim(snmp_get($device, '1.3.6.1.4.1.674.10893.2.31.500.1.3.0', '-OQv'));
$hardware = trim(snmp_get($device, '1.3.6.1.4.1.674.10893.2.31.500.1.5.0', '-OQv'));
