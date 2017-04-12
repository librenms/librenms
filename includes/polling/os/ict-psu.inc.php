<?php

//SNMPv2-SMI::enterprises.39145.11.1.0 = STRING: "ICT600-48"
//SNMPv2-SMI::enterprises.39145.11.4.0 = STRING: "1.08"

$hardware = trim(snmp_get($device, '1.3.6.1.4.1.39145.11.1.0', '-OQv', '', ''), '" ');
$version = 'v' . trim(snmp_get($device, '1.3.6.1.4.1.39145.11.4.0', '-OQv', '', ''), '" ');
