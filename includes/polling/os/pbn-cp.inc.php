<?php

// for PBN CPE 120/121
$version = str_replace(['"'], '', snmp_get($device, '1.3.6.1.4.1.11606.24.1.1.6.0', '-Ovq'));
$hardware = str_replace(['"'], '', snmp_get($device, '1.3.6.1.4.1.11606.24.1.1.7.0', '-Ovq'));
$features = str_replace(['"'], '', snmp_get($device, '1.3.6.1.4.1.11606.24.1.1.10.0', '-Ovq'));
// normalize MAC address (serial)
$serial = str_replace([' ', ':', '-', '"'], '', strtolower(snmp_get($device, '1.3.6.1.4.1.11606.24.1.1.4.0', '-Ovq')));
