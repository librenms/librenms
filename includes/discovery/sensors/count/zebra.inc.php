<?php

$oids = [
    0 => [
        'type' => 'zebra-printed-length-life',
        'descr' => 'Printed Length (life, INCHES)',
        'oid' => '.1.3.6.1.4.1.10642.200.17.3.0',
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
$rrd_type = 'GAUGE';

$current_time = time();

$tmp_snmp_multi = snmp_get_multi_oid($device, array_column($oids, 'oid'));

foreach ($oids as $index => $entry) {
    $oid = $entry['oid'];
    $type = $entry['type'];
    $descr = $entry['descr'];

    if (! isset($tmp_snmp_multi[$oid])) {
        continue;
    }
    $current = $tmp_snmp_multi[$oid];

    if (! empty($current) && $current !== 'FULL:0') {
        discover_sensor(
            null,
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
            $group,
            $rrd_type
        );
    }
}

unset($oids, $current_time, $class, $divisor, $multiplier, $low_limit, $low_warn_limit, $warn_limit, $high_limit, $poller_type, $entPhysicalIndex, $entPhysicalIndex_measured, $user_func, $group, $oid, $type, $descr, $current, $epoch_time);
