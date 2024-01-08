<?php

/*
 *
 * OIDs obtained from First Network Group Inc. DHCPatriot operations manual version 6.4.x
 * Found here: http://www.network1.net/products/dhcpatriot/documentation/PDFs/v64xmanual-rev1.pdf
 *
*/

$oids = [
    0 => [
        'descr' => 'System Software Health',
        'oid'   => '.1.3.6.1.4.1.2021.51.9.4.1.2.6.72.69.65.76.84.72.1',
    ],
    1 => [
        'descr' => 'DHCPv4',
        'oid'   => '.1.3.6.1.4.1.2021.52.6.4.1.2.4.68.72.67.80.1',
    ],
    2 => [
        'descr' => 'DHCPv6',
        'oid'   => '.1.3.6.1.4.1.2021.52.9.4.1.2.5.68.72.67.80.54.1',
    ],
    3 => [
        'descr' => 'DNS',
        'oid'   => '.1.3.6.1.4.1.2021.52.1.4.1.2.3.68.78.83.1',
    ],
    4 => [
        'descr' => 'HTTP',
        'oid'   => '.1.3.6.1.4.1.2021.52.2.4.1.2.4.72.84.84.80.1',
    ],
    5 => [
        'descr' => 'HTTPS',
        'oid'   => '.1.3.6.1.4.1.2021.52.3.4.1.2.5.72.84.84.80.83.1',
    ],
    6 => [
        'descr' => 'NTP',
        'oid'   => '.1.3.6.1.4.1.2021.52.4.4.1.2.3.78.84.80.1',
    ],
    7 => [
        'descr' => 'SSH',
        'oid'   => '.1.3.6.1.4.1.2021.52.5.4.1.2.3.83.83.72.1',
    ],
    8 => [
        'descr' => 'Database Status',
        'oid'   => '.1.3.6.1.4.1.2021.51.2.4.1.2.5.77.89.83.81.76.1',
    ],
    9 => [
        'descr' => 'Database Sync Status',
        'oid'   => '.1.3.6.1.4.1.2021.51.3.4.1.2.16.77.89.83.81.76.82.69.80.76.73.67.65.84.73.79.78.1',
    ],
];

$class = 'state';
$type = 'dhcpatriotServiceStatus';
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
    $descr = $entry['descr'];

    if (! empty($tmp_snmp_multi) && gettype($tmp_snmp_multi[$oid]) === 'string') {
        $tmp_data = explode(':', $tmp_snmp_multi[$oid]);

        $current = intval($tmp_data[1]);

        if (abs(intval($tmp_data[0]) - $current_time) > 300) {
            $current = 2;
        }

        if ($tmp_data[1] === '999') {
            $current = 3;
        }

        $states = [
            ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'serviceUp'],
            ['value' => 2, 'generic' => 2, 'graph' => 0, 'descr' => 'serviceNotUpdatedWithinLast5Min'],
            ['value' => 3, 'generic' => 2, 'graph' => 0, 'descr' => 'serviceDown'],
        ];
    }

    create_state_index($type, $states);

    if (! empty($current)) {
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
    create_sensor_to_state_index($device, $type, $index);
}

unset($class, $oid, $index, $type, $descr, $divisor, $multiplier, $low_limit, $low_warn_limit, $warn_limit, $high_limit, $current, $poller_type, $entPhysicalIndex, $entPhysicalIndex_measured, $user_func, $group, $oids, $current_time, $tmp_snmp_multi, $tmp_data, $states);
