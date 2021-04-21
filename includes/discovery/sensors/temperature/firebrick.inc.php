<?php

$monitoringMib = snmpwalk_cache_multi_oid($device, 'fbMonitoringMib', [], 'FIREBRICK-MONITORING', 'firebrick');
$params_map = [
    'Fan Controller' => [
        'description' => 'Fan Controller',
        'crit_low' => 10,
        'warn_low' => 20,
        'warn_high' => 50,
        'crit_high' => 70, ],
    'CPU' => [
        'description' => 'CPU',
        'crit_low' => 10,
        'warn_low' => 20,
        'warn_high' => 60,
        'crit_high' => 80, ],
    'RTC' => [
        'description' => 'Real-time Clock',
        'crit_low' => 10,
        'warn_low' => 20,
        'warn_high' => 50,
        'crit_high' => 70, ],
    'PCB' => [
        'description' => 'Internal Ambient',
        'crit_low' => 10,
        'warn_low' => 20,
        'warn_high' => 50,
        'crit_high' => 70, ],
];

foreach ($monitoringMib as $idx => $mibEntry) {
    if (isset($params_map[$mibEntry['fbMonReadingName']])) {
        $cfg = $params_map[$mibEntry['fbMonReadingName']];
        discover_sensor(
            $valid['sensor'],
            'temperature',
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
