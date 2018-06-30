<?php

$hardware = trim(snmp_get($device, '1.3.6.1.4.1.4458.1000.1.1.30.0', '-OQv', '', ''), '"');
$version = trim(snmp_get($device, '1.3.6.1.4.1.4458.1000.1.1.3.0', '-OQv', '', ''), '"');
$features = 'Ver. '.trim(snmp_get($device, '1.3.6.1.4.1.4458.1000.1.1.2.0', '-OQv', '', ''), '"');
$serial = trim(snmp_get($device, '1.3.6.1.4.1.4458.1000.1.1.29.0', '-OQv', '', ''), '"');
