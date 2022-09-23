<?php

echo 'Sub10 temperature ';

// Get Current Value
$temp_oid = 'sub10UnitLclMWUTemperature.0';
[$oid, $current] = explode(' ', snmp_get($device, $temp_oid, '-OsqnU', 'SUB10SYSTEMS-MIB'));

// Get Alarm Ranges
$alarm_oid = 'sub10UnitMgmtAlarmName';
$alarms = snmp_walk($device, $alarm_oid, '-OsqU', 'SUB10SYSTEMS-MIB');
$indexes = [];
foreach (explode("\n", $alarms) as $alarm) {
    if (preg_match('/^\w+\.(\d) MWU Temperature (.*)$/', $alarm, $matches)) {
        $indexes[strtolower($matches[2])] = $matches[1];
    }
}

$thresh_oid = 'sub10UnitMgmtAlarmRaiseThresh';
$threshes = snmp_walk($device, $thresh_oid, '-OsqU', 'SUB10SYSTEMS-MIB');
$thresholds = [];
foreach (explode("\n", $threshes) as $thresh) {
    preg_match('/^\w+\.(\d) (.*)$/', $thresh, $matches);
    $thresholds[$matches[1]] = $matches[2];
}

// Create Sensor
discover_sensor($valid['sensor'], 'temperature', $device, $oid, $oid, 'sub10', 'Modem', '1', '1', $thresholds[$indexes['low']], null, null, $thresholds[$indexes['high']], $current);
