<?php

$firmware_oid=$poll_device['sysObjectID'].'.5.1.1.0';
$hardware_oid=$poll_device['sysObjectID'].'.5.1.5.0';

$version  = snmp_get($device, $firmware_oid, '-Oqv');
$hardware = $poll_device['sysDescr'].' '.str_replace('"', '', snmp_get($device, $hardware_oid, '-Oqv'));
