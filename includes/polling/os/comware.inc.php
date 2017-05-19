<?php

// SNMPv2-MIB::sysDescr.0 = STRING: Hangzhou H3C Comware Platform Software, Software Version 3.10, Release 2211P06
// H3C S3100-8TP-EI
// Copyright(c) 2004-2010 Hangzhou H3C Tech. Co.,Ltd. All rights reserved.
// SNMPv2-MIB::sysObjectID.0 = OID: HH3C-PRODUCT-ID-MIB::hh3c-S3100-8TP-EI
echo 'Comware OS...';

$hardware = snmp_get($device, 'sysObjectID.0', '-Osqv', 'SNMPv2-MIB:HH3C-PRODUCT-ID-MIB');

preg_match('/Version ([0-9.]+).*Release ([0-9P]+)/', $poll_device['sysDescr'], $version_match);
$version = $version_match[1];
$features = $version_match[2];

$serial_nums = explode("\n", trim(snmp_walk($device, 'hh3cEntityExtManuSerialNum', '-Osqv', 'HH3C-ENTITY-EXT-MIB')));
$serial = $serial_nums[0]; // use the first s/n
