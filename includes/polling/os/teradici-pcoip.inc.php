<?php

//SNMPv2-SMI::enterprises.25071.1.2.6.1.1.5.1 = STRING: "TERA2240 revision 1.0 (512 MB)"
//SNMPv2-SMI::enterprises.25071.1.2.6.1.1.6.1 = STRING: "AHK TERA2240 4x DP Host Card Copper"
//SNMPv2-SMI::enterprises.25071.1.2.6.1.1.7.1 = STRING: "DS12144FA"
//SNMPv2-SMI::enterprises.25071.1.2.6.1.1.8.1 = STRING: "DXM13-9 PV2.0.D"
//SNMPv2-SMI::enterprises.25071.1.2.6.1.1.9.1 = STRING: "4.7.5"

$hardware = trim(snmp_get($device, '1.3.6.1.4.1.25071.1.2.6.1.1.5.1', '-OQv'), '" ');
$version = trim(snmp_get($device, '1.3.6.1.4.1.25071.1.2.6.1.1.9.1', '-OQv'), '" ');
$serial = trim(snmp_get($device, '1.3.6.1.4.1.25071.1.2.6.1.1.7.1', '-OQv'), '" ');
