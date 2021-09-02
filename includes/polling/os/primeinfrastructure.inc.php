<?php

$version = preg_replace('/Cisco ADE-OS /', '', snmp_get($device, '1.3.6.1.2.1.47.1.1.1.1.10.1', '-OQv'));
$serial = trim(snmp_get($device, '1.3.6.1.2.1.47.1.1.1.1.11.1', '-Ovq'), '"');
$hardware = trim(snmp_get($device, '1.3.6.1.2.1.47.1.1.1.1.13.1', '-Ovq'), '"');
