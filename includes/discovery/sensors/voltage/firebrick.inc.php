<?php

$monitoringMib = snmpwalk_cache_multi_oid($device, 'fbMonitoringMib', [], 'FIREBRICK-MONITORING', 'firebrick');
$params_map = [
    '1.1V' => [
        'description' => '1.1V Reference Voltage',
        'crit_low' => 1.0,
        'warn_low' => 1.05,
        'warn_high' => 1.15,
        'crit_high' => 1.2, ],
    '1.325V' => [
        'description' => '1.325V Reference Voltage',
        'crit_low' => 1.315,
        'warn_low' => 1.32,
        'warn_high' => 1.33,
        'crit_high' => 1.335, ],
    '1.35V' => [
        'description' => '1.35V Reference Voltage',
        'crit_low' => 1.25,
        'warn_low' => 1.30,
        'warn_high' => 1.40,
        'crit_high' => 1.45, ],
    '3.3V' => [
        'description' => '3.3V Reference Voltage',
        'crit_low' => 3.2,
        'warn_low' => 3.25,
        'warn_high' => 3.35,
        'crit_high' => 3.4, ],
    '5.0V' => [
        'description' => '5.0V Reference Voltage',
        'crit_low' => 4.9,
        'warn_low' => 4.95,
        'warn_high' => 5.05,
        'crit_high' => 5.10, ],
    '12V' => [
        'description' => 'Power Supply Voltage',
        'crit_low' => 11.9,
        'warn_low' => 11.95,
        'warn_high' => 12.05,
        'crit_high' => 12.1, ],
    'TRNG' => [
        'description' => 'True Random Number Generator Voltage',
        'crit_low' => 1.0,
        'warn_low' => 1.05,
        'warn_high' => 12.15,
        'crit_high' => 12.2, ],
    '12V-A' => [
        'description' => 'PSU A Output Voltage',
        'crit_low' => 10.0,
        'warn_low' => 11.4,
        'warn_high' => 12.6,
        'crit_high' => 14.0, ],
    '12V-B' => [
        'description' => 'PSU B Output Voltage',
        'crit_low' => 10.0,
        'warn_low' => 11.4,
        'warn_high' => 12.6,
        'crit_high' => 14.0, ],
    '12V-Common' => [
        'description' => '+12V',
        'crit_low' => 10.0,
        'warn_low' => 11.4,
        'warn_high' => 12.6,
        'crit_high' => 14.0, ],
    '1.8V' => [
        'description' => '+1.8V',
        'crit_low' => 1.7,
        'warn_low' => 1.75,
        'warn_high' => 1.85,
        'crit_high' => 1.90, ],
    '1.2V' => [
        'description' => '+1.2V',
        'crit_low' => 1.1,
        'warn_low' => 1.15,
        'warn_high' => 1.25,
        'crit_high' => 1.3, ],
    '1.1V' => [
        'description' => '+1.1V',
        'crit_low' => 0.9,
        'warn_low' => 1.0,
        'warn_high' => 1.2,
        'crit_high' => 1.3, ],
    '3.3V(Fan)' => [
        'description' => '+3.3V Fan Power',
        'crit_low' => 3.2,
        'warn_low' => 3.25,
        'warn_high' => 3.35,
        'crit_high' => 3.4, ],
    '1.2V(Fan)' => [
        'description' => '+1.2V Fan Power',
        'crit_low' => 1.0,
        'warn_low' => 1.1,
        'warn_high' => 1.3,
        'crit_high' => 1.4, ],
];

foreach ($monitoringMib as $idx => $mibEntry) {
    if (isset($params_map[$mibEntry['fbMonReadingName']])) {
        $cfg = $params_map[$mibEntry['fbMonReadingName']];
        discover_sensor(
            $valid['sensor'],
            'voltage',
            $device,
            '.1.3.6.1.4.1.24693.100.1.1.1.4.' . $idx,
            $idx,
            'firebrick',
            $cfg['description'],
            (isset($cfg['divisor']) ? $cfg['divisor'] : '1000'),
            '1',
            (isset($cfg['crit_low']) ? $cfg['crit_low'] : 0),
            (isset($cfg['warn_low']) ? $cfg['warn_low'] : 0),
            (isset($cfg['warn_high']) ? $cfg['warn_high'] : 15),
            (isset($cfg['crit_high']) ? $cfg['crit_high'] : 20),
            );
    }
}
