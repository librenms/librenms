<?php

/*
 *
 * OIDs obtained from First Network Group Inc. DHCPatriot operations manual version 6.4.x
 * Found here: http://www.network1.net/products/dhcpatriot/documentation/PDFs/v64xmanual-rev1.pdf
 *
*/

$oids = [
    0 => [
        'type'  => 'dhcpatriotDatabaseThreads',
        'descr' => 'Database Threads',
        'oid'   => '.1.3.6.1.4.1.2021.50.45',
    ],
    1 => [
        'type'  => 'dhcpatriotDatabaseQueriesPerSecond',
        'descr' => 'Database Queries Per Second',
        'oid'   => '.1.3.6.1.4.1.2021.50.46',
    ],
    2 => [
        'type'  => 'dhcpatriotDHCPv4LeasesPerSecond',
        'descr' => 'DHCPv4 Leases Per Second',
        'oid'   => '.1.3.6.1.4.1.2021.50.70',
    ],
    3 => [
        'type'  => 'dhcpatriotDHCPv6LeasesPerSecond',
        'descr' => 'DHCPv6 Leases Per Second',
        'oid'   => '.1.3.6.1.5.1.2021.50.140',
    ],
    4 => [
        'type'  => 'dhcpatriotLicenseExpiration',
        'descr' => 'License Expiration Days Remaining',
        'oid'   => '.1.3.6.1.4.1.2021.51.12.4.1.2.7.76.73.67.69.78.83.69.1',
    ],
];

$class = 'count';
$divisor = 1;
$multiplier = 1;
$low_limit = null;
$low_warn_limit = null;
$warn_limit = null;
$high_limit = null;
$poller_type = 'snmp';
$entPhysicalIndex = null;
$entPhysicalIndex_measured = null;
$user_func = null;
$group = null;

$current_time = time();

$tmp_snmp_multi = snmp_get_multi_oid($device, array_column($oids, 'oid'));

foreach ($oids as $index => $entry) {
    $oid = $entry['oid'];
    $type = $entry['type'];
    $descr = $entry['descr'];

    $current = $tmp_snmp_multi[$oid];

    if ($type === 'dhcpatriotLicenseExpiration' && $current !== 'FULL:0' && gettype($current) === 'string') {
        $epoch_time = explode(':', $current);
        $current = round((intval($epoch_time[1]) - $current_time) / (60 * 60 * 24));
    }

    if (! empty($current) && $current !== 'FULL:0') {
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
            $warn_limit,
            $high_limit,
            $current,
            $poller_type,
            $entPhysicalIndex,
            $entPhysicalIndex_measured,
            $user_func,
            $group
        );
    }
}

unset($oids, $current_time, $class, $divisor, $multiplier, $low_limit, $low_warn_limit, $warn_limit, $high_limit, $poller_type, $entPhysicalIndex, $entPhysicalIndex_measured, $user_func, $group, $oid, $type, $descr, $current, $epoch_time);
