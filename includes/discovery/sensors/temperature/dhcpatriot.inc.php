<?php

/*
 *
 * OIDs obtained from First Network Group Inc. DHCPatriot operations manual version 6.4.x
 * Found here: http://www.network1.net/products/dhcpatriot/documentation/PDFs/v64xmanual-rev1.pdf
 *
*/

$class = 'temperature';
$oid = '.1.3.6.1.4.1.2021.50.3.101.1';
$index = 1;
$type = 'dhcpatriotTempCPU';
$descr = 'CPU';
$divisor = 1;
$multiplier = 1;
$low_limit = 5;
$low_warn_limit = 10;
$high_warn_limit = 40;
$high_limit = 45;
$current = snmp_get($device, $oid, '-Oqv');
$poller_type = 'snmp';
$entPhysicalIndex = null;
$entPhysicalIndex_measured = null;
$user_func = null;
$group = null;

if (! empty($current) && is_numeric($current)) {
    discover_sensor(
        $valid['sensor'],
        $class,
        $device,
        $oid,
        $index,
        $type,
        $descr,
        $divisor,
        $multiplier,
        $low_limit,
        $low_warn_limit,
        $high_warn_limit,
        $high_limit,
        $current,
        $poller_type,
        $entPhysicalIndex,
        $entPhysicalIndex_measured,
        $user_func,
        $group
    );
}

unset($class, $oid, $index, $type, $descr, $divisor, $multiplier, $low_limit, $low_warn_limit, $warn_limit, $high_limit, $current, $poller_type, $entPhysicalIndex, $entPhysicalIndex_measured, $user_func, $group);
