<?php

$hardware = trim(snmp_get($device, '.1.3.6.1.4.1.16068.2.4.0', '-OQv', 'REPEATIT-MIB DEFINITIONS', ''), '"');
$version = trim(snmp_get($device, '.1.3.6.1.4.1.16068.2.5.0', '-OQv', 'REPEATIT-MIB DEFINITIONS', ''), '"');
//$features = 'Ver. '.trim(snmp_get($device, '1.3.6.1.4.1.4458.1000.1.1.2.0', '-OQv', 'REPEATIT-MIB DEFINITIONS', ''), '"');
$serial = trim(snmp_get($device, '.1.3.6.1.4.1.16068.2.6.0', '-OQv', 'REPEATIT-MIB DEFINITIONS', ''), '"');
