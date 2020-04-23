<?php

/*
 *
 * OIDs obtained from First Network Group Inc. DHCPatriot operations manual version 6.4.x
 * Found here: http://www.network1.net/products/dhcpatriot/documentation/PDFs/v64xmanual-rev1.pdf
 *
*/

$oids = array(
    1 => array(
        "type"  => "dhcpatriotServiceStatus",
        "descr" => "System Software Health",
        "oid"   => ".1.3.6.1.4.1.2021.51.9.4.1.2.6.72.69.65.76.84.72.1"
    ),
    2 => array(
        "type"  => "dhcpatriotServiceStatus",
        "descr" => "DHCPv4",
        "oid"   => ".1.3.6.1.4.1.2021.52.6.4.1.2.4.68.72.67.80.1"
    ),
    3 => array(
        "type"  => "dhcpatriotServiceStatus",
        "descr" => "DHCPv6",
        "oid"   => ".1.3.6.1.4.1.2021.52.9.4.1.2.5.68.72.67.80.54.1"
    ),
    4 => array(
        "type"  => "dhcpatriotServiceStatus",
        "descr" => "DNS",
        "oid"   => ".1.3.6.1.4.1.2021.52.1.4.1.2.3.68.78.83.1"
    ),
    5 => array(
        "type"  => "dhcpatriotServiceStatus",
        "descr" => "HTTP",
        "oid"   => ".1.3.6.1.4.1.2021.52.2.4.1.2.4.72.84.84.80.1"
    ),
    6 => array(
        "type"  => "dhcpatriotServiceStatus",
        "descr" => "HTTPS",
        "oid"   => ".1.3.6.1.4.1.2021.52.3.4.1.2.5.72.84.84.80.83.1"
    ),
    7 => array(
        "type"  => "dhcpatriotServiceStatus",
        "descr" => "NTP",
        "oid"   => ".1.3.6.1.4.1.2021.52.4.4.1.2.3.78.84.80.1"
    ),
    8 => array(
        "type"  => "dhcpatriotServiceStatus",
        "descr" => "SSH",
        "oid"   => ".1.3.6.1.4.1.2021.52.5.4.1.2.3.83.83.72.1"
    ),
    9 => array(
        "type"  => "dhcpatriotServiceStatus",
        "descr" => "Database Status",
        "oid"   => ".1.3.6.1.4.1.2021.51.2.4.1.2.5.77.89.83.81.76.1"
    ),
    10 => array(
        "type"  => "dhcpatriotServiceStatus",
        "descr" => "Database Sync Status",
        "oid"   => ".1.3.6.1.4.1.2021.51.3.4.1.2.16.77.89.83.81.76.82.69.80.76.73.67.65.84.73.79.78.1"
    ),
    11 => array(
        "type"  => "dhcpatriotSystemTime",
        "descr" => "System Time",
        "oid"   => ".1.3.6.1.4.1.2021.51.13.4.1.2.4.84.73.77.69.1"
    )
);

$class = 'state';
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

foreach ($oids as $index => $entry) {
    $oid = $oids[$index]['oid'];
    $type = $oids[$index]['type'];
    $descr = $oids[$index]['descr'];

    $tmp_snmp_data = snmp_get($device, $oid, '-Oqv');

    if ($type === 'dhcpatriotServiceStatus') {
        $tmp_snmp_data = explode(':', $tmp_snmp_data);
        $current = intval($tmp_snmp_data[1]);

        if ((intval($tmp_snmp_data[0]) - $current_time) > 300) {
            $current = 2;
        }

        if ($tmp_snmp_data[1] === '999') {
            $current = 3;
        }

        $states = [
            ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'serviceUp'],
            ['value' => 2, 'generic' => 1, 'graph' => 0, 'descr' => 'serviceNotUpdatedWithinLast5Min'],
            ['value' => 3, 'generic' => 2, 'graph' => 0, 'descr' => 'serviceDown'],
        ];
    }

    if ($type === 'dhcpatriotSystemTime') {
        $current = 1;
        if ((intval($tmp_snmp_data) - $current_time) > 300) {
            $current = 3;
        }

        $states = [
            ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'systemTimeOK'],
            ['value' => 3, 'generic' => 2, 'graph' => 0, 'descr' => 'systemTimeOutOfSync'],
        ];
    }
    create_state_index($type, $states);

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

    create_sensor_to_state_index($device, $type, $index);
}

unset($current_time, $tmp_snmp_data, $states, $class, $oid, $index, $type, $descr, $divisor, $multiplier, $low_limit, $low_warn_limit, $warn_limit, $high_limit, $current, $poller_type, $entPhysicalIndex, $entPhysicalIndex_measured, $user_func, $group);
