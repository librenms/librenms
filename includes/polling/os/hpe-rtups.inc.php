<?php

$rtups_data = snmp_get_multi_oid($device, ['deviceSerialNumber.0', 'deviceFirmwareVersion.0'], '-OQs', 'CPQPOWER-MIB');

$serial = $rtups_data['deviceSerialNumber.0'];
$version = $rtups_data['deviceFirmwareVersion.0'];

unset($rtups_data);
