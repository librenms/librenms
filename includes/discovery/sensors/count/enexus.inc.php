<?php

use Illuminate\Support\Str;

// Workaround for missing value in SP2-MIB and LibreNMS does not create sensor without it
if (Str::startsWith($device['sysObjectID'], '.1.3.6.1.4.1.12148.')) {
    $batteryTestDuration = snmp_get($device, 'SP2-MIB::batteryTest.5.0', '-Ovq');
    if ($batteryTestDuration === false) {
        return;
    }
    discover_sensor(
        null,
        'count',
        $device,
        '.1.3.6.1.4.1.12148.10.10.16.5.0', // SP2-MIB::batteryTestDuration.0
        'batteryTestDuration.0',
        'batteryTestDuration',
        'Battery Test Max Duration (minutes)',
        1,
        1,
        null,
        null,
        null,
        null,
        $batteryQualityResult,
        'snmp',
        null,
        null,
        null,
        null,
        'gauge'
    );

    unset($batteryTestDuration);
}