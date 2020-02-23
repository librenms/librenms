<?php

//SNMPv2-SMI::enterprises.39145.13.1.0 = STRING: "Modular Power Series 48V"
//SNMPv2-SMI::enterprises.39145.13.4.0 = STRING: "1.04"

$hardware = trim(snmp_get($device, '1.3.6.1.4.1.39145.13.1.0', '-OQv', '', ''), '" ');
$version = 'v' . trim(snmp_get($device, '1.3.6.1.4.1.39145.13.4.0', '-OQv', '', ''), '" ');
