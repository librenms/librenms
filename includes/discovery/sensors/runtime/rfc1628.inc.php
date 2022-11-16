<?php

echo 'RFC1628 ';

// UPS-MIB::upsSecondsOnBattery
$secs_on_battery = SnmpQuery::get('UPS-MIB::upsSecondsOnBattery.0')->value();

if (is_numeric($secs_on_battery)) {
    $divisor = get_device_divisor($device, $pre_cache['poweralert_serial'] ?? '', 'runtime', '.1.3.6.1.2.1.33.1.2.2.0');
    discover_sensor(
        $valid['sensor'],
        'runtime',
        $device,
        '.1.3.6.1.2.1.33.1.2.2.0',
        100,
        'rfc1628',
        'Time on battery',
        $divisor,
        1,
        0,
        0,
        1,
        2,
        $secs_on_battery / $divisor
    );
}

// UPS-MIB::upsEstimatedMinutesRemaining
$est_battery_time = SnmpQuery::get('UPS-MIB::upsEstimatedMinutesRemaining.0')->value();

if (is_numeric($est_battery_time)) {
    $divisor = get_device_divisor($device, $pre_cache['poweralert_serial'] ?? '', 'runtime', '.1.3.6.1.2.1.33.1.2.3.0');
    discover_sensor(
        $valid['sensor'],
        'runtime',
        $device,
        '.1.3.6.1.2.1.33.1.2.3.0',
        200,
        'rfc1628',
        'Estimated battery time remaining',
        $divisor,
        1,
        5,
        10,
        null,
        10000,
        $est_battery_time / $divisor
    );
}

unset($secs_on_battery, $est_battery_time);
