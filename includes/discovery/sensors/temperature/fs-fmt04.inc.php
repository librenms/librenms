<?php

/**
 * FS FMT / OAP — SFP module temperature (OAP-C1-OEO vSFP*ModeTemperature = sub-id 9).
 *
 * Display-hints in the agent are disabled in LibreNMS; vendor stores °C × 100 here.
 * Skip lanes with vSFP*State (.1) = off — matches dBm discovery.
 * EDFA module temperature: `…10.16.2.1.22` (°C×100) when `.1.0` = on — not `.27` (that is PUMP cooling ×100 mA; see `count/fs-fmt04.inc.php`).
 */
echo 'FS FMT OAP temperature ';

$suites = [
    '1.2' => 'OEO1',
    '3.2' => 'OEO2',
];

$slots = [
    11 => 'A1',
    12 => 'A2',
    13 => 'B1',
    14 => 'B2',
    15 => 'C1',
    16 => 'C2',
    17 => 'D1',
    18 => 'D2',
];

$sensor_type = 'fs-fmt04';
// Raw OID is °C × 100 (see OAP-C1-OEO vSFP*ModeTemperature); poller applies sensor_divisor.
$divisor = 100;

// Explicit °C limits (overwrite LibreNMS guessLimits, which used raw SNMP and stored ~4021/4051).
// Adjust in the UI per site policy. Typical DOM case / module temp operating envelope.
$limit_low = 0.0;
$limit_low_warn = 5.0;
$limit_warn = 75.0;
$limit_high = 85.0;

foreach ($suites as $suiteOid => $suiteLabel) {
    foreach ($slots as $port => $slotLabel) {
        $base = '.1.3.6.1.4.1.40989.10.16.' . $suiteOid . '.' . $port;
        $stateOid = $base . '.1.0';
        $laneOn = SnmpQuery::get($stateOid)->value();
        if (! is_numeric($laneOn) || (int) $laneOn !== 1) {
            continue;
        }

        $oid = $base . '.9.0';
        $raw = SnmpQuery::get($oid)->value();

        if (is_numeric($raw)) {
            $descr = $suiteLabel . ' ' . $slotLabel . ' module temp';
            $index = 'fmt04-temp-' . str_replace('.', '-', (string) $suiteOid) . '-' . $port;
            // Store scaled value at discovery so UI matches before first poll; polling still divides raw SNMP by $divisor.
            $scaled = (float) $raw / $divisor;
            discover_sensor(
                null,
                'temperature',
                $device,
                $oid,
                $index,
                $sensor_type,
                $descr,
                $divisor,
                1,
                $limit_low,
                $limit_low_warn,
                $limit_warn,
                $limit_high,
                $scaled,
                'snmp'
            );
        }
    }
}

// --- FMT20PA-EDFA module temperature: `…2.1.22.0` (°C ×100); correlates with web UI “Module temperature”. ---
$edfa = '.1.3.6.1.4.1.40989.10.16.2.1';
$edfaState = SnmpQuery::get($edfa . '.1.0')->value();
if (is_numeric($edfaState) && (int) $edfaState === 1) {
    $oid = $edfa . '.22.0';
    $raw = SnmpQuery::get($oid)->value();
    if (is_numeric($raw)) {
        $scaled = (float) $raw / $divisor;
        $index = 'fmt04-edfa-module-temp';
        discover_sensor(
            null,
            'temperature',
            $device,
            $oid,
            $index,
            $sensor_type,
            'EDFA module temperature',
            $divisor,
            1,
            $limit_low,
            $limit_low_warn,
            $limit_warn,
            $limit_high,
            $scaled,
            'snmp'
        );
    }
}
