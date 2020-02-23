<?php

//SNMPv2-SMI::enterprises.39145.12.1.0 = STRING: "ICT1500-48SW"
//SNMPv2-SMI::enterprises.39145.12.4.0 = STRING: "1.03"

$hardware = trim(snmp_get($device, '1.3.6.1.4.1.39145.12.1.0', '-OQv', '', ''), '" ');
$version = 'v' . trim(snmp_get($device, '1.3.6.1.4.1.39145.12.4.0', '-OQv', '', ''), '" ');
