<?php

//SNMPv2-SMI::enterprises.39145.10.1.0 = STRING: "ICT180S-12BRCP" -- deviceModel
//SNMPv2-SMI::enterprises.39145.10.4.0 = STRING: "2.08" -- software version

$hardware = trim(snmp_get($device, '1.3.6.1.4.1.39145.10.1.0', '-OQv', '', ''), '" ');
$version = 'v' . trim(snmp_get($device, '1.3.6.1.4.1.39145.10.4.0', '-OQv', '', ''), '" ');
