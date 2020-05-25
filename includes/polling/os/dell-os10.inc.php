<?php

// DELLEMC-OS10-PRODUCTS-MIB
$dell_os10_hardware = snmp_get_multi($device, ['os10ChassisType.1', 'os10ChassisHwRev.1', 'os10ChassisServiceTag.1', 'os10ChassisExpServiceCode.1', 'os10ChassisProductSN.1'], '-OQUs', 'DELLEMC-OS10-CHASSIS-MIB');

$hardware = $dell_os10_hardware[1]['os10ChassisType'];
$version = $dell_os10_hardware[1]['os10ChassisHwRev'];
$serial = $dell_os10_hardware[1]['os10ChassisProductSN'];
$features = $dell_os10_hardware[1]['os10ChassisServiceTag'] . '/' . $dell_os10_hardware[1]['os10ChassisExpServiceCode'];
