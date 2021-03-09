<?php

$monitoringMib = snmpwalk_cache_multi_oid($device, 'fbMonitoringMib', [], 'FIREBRICK-MONITORING', 'firebrick');
$params_map = [
    'Fan1' => [
        'description' => 'Fan 1',
        'crit_low' => 1000,
        'warn_low' => 2000,
        'warn_high' => 6000,
        'crit_high' => 8000, ],
    'Fan2' => [
        'description' => 'Fan 2',
        'crit_low' => 1000,
        'warn_low' => 2000,
        'warn_high' => 6000,
        'crit_high' => 8000, ],
];

foreach ($monitoringMib as $idx => $mibEntry) {
    if (isset($params_map[$mibEntry['fbMonReadingName']])) {
        $cfg = $params_map[$mibEntry['fbMonReadingName']];
        discover_sensor(
            $valid['sensor'],
            'fanspeed',
            $device,
            '.1.3.6.1.4.1.24693.100.1.1.1.4.' . $idx,
            $idx,
            'firebrick',
            $cfg['description'],
            (isset($cfg['divisor']) ? $cfg['divisor'] : '1'),
            '1',
            (isset($cfg['crit_low']) ? $cfg['crit_low'] : 0),
            (isset($cfg['warn_low']) ? $cfg['warn_low'] : 0),
            (isset($cfg['warn_high']) ? $cfg['warn_high'] : 15),
            (isset($cfg['crit_high']) ? $cfg['crit_high'] : 20),
            );
    }
}
