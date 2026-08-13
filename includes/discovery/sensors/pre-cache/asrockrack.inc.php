<?php

/**
 * LibreNMS - ASRock Rack BMC sensor pre-cache
 *
 * Walks the SensorInfo tree (.1.3.6.1.4.1.49622.6) once and groups sensors
 * by class for the per-class discovery includes.
 *
 * Tree layout: .6.<index>.<field>.0 with fields
 * 3=name, 5=reading ("54.00 deg_c"), 6..11=LowNRT,LowCT,LowNCT,UpNCT,UpCT,UpNRT
 * The MIB module name is board-specific, so raw numeric OIDs are used.
 * The unit word in the reading string determines the sensor class.
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */
$asrock_units = [
    'volts' => 'voltage',
    'deg_c' => 'temperature',
    'rpm' => 'fanspeed',
    'amps' => 'current',
    'watts' => 'power',
];

$asrock_raw = [];
foreach (SnmpQuery::numeric()->walk('.1.3.6.1.4.1.49622.6')->values() as $oid => $value) {
    if (preg_match('/^\.1\.3\.6\.1\.4\.1\.49622\.6\.(\d+)\.(\d+)\.0$/', $oid, $matches)) {
        $asrock_raw[$matches[1]][$matches[2]] = $value;
    }
}

foreach ($asrock_raw as $asrock_index => $asrock_sensor) {
    [$asrock_value, $asrock_unit] = array_pad(explode(' ', $asrock_sensor[5] ?? ''), 2, '');
    $asrock_class = $asrock_units[$asrock_unit] ?? null;

    // ponytail: skip sensors without a known unit, reading zero (absent hardware: unpopulated
    // M.2/GPU/fan headers) or reading >= 0x8000 (IPMI discrete sensors like STS_PSU*);
    // map discrete sensors to state sensors if event monitoring is wanted
    if ($asrock_class === null || ! is_numeric($asrock_value) || $asrock_value == 0 || $asrock_value >= 32768) {
        continue;
    }

    $asrock_threshold = fn ($field) => is_numeric($num = strtok($asrock_sensor[$field] ?? '', ' ')) ? $num : null;

    $pre_cache['asrockrack'][$asrock_class][] = [
        'oid' => ".1.3.6.1.4.1.49622.6.$asrock_index.5.0",
        'index' => $asrock_index,
        'descr' => str_replace('_', ' ', preg_replace('/^(VOLT|TEMP|CUR|PWR)_/', '', $asrock_sensor[3] ?? "Sensor $asrock_index")),
        'current' => $asrock_value,
        'low_limit' => $asrock_threshold(7) ?? $asrock_threshold(6),
        'low_warn' => $asrock_threshold(8),
        'warn' => $asrock_threshold(9),
        'high' => $asrock_threshold(10) ?? $asrock_threshold(11),
    ];
}

unset($asrock_units, $asrock_raw, $asrock_index, $asrock_sensor, $asrock_value, $asrock_unit, $asrock_class, $asrock_threshold);
