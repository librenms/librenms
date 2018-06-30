<?php

// SNMPv2-SMI::enterprises.1602.1.1.1.1.0 = STRING: "MF8500C Series"
$hardware = trim(snmp_get($device, 'SNMPv2-SMI::enterprises.1602.1.1.1.1.0', '-OQv'), '" ');

// SNMPv2-SMI::mib-2.43.5.1.1.17.1 = STRING: "QJY44712"
$serial = trim(snmp_get($device, 'SNMPv2-SMI::mib-2.43.5.1.1.17.1', '-OQv'), '" ');

// SNMPv2-SMI::enterprises.1602.1.1.1.4.0 = STRING: "03.03"
$version = trim(snmp_get($device, 'SNMPv2-SMI::enterprises.1602.1.1.1.4.0', '-OQv'), '" ');

// Strip off useless brand fields
$hardware = str_ireplace(' Series', '', $hardware);
