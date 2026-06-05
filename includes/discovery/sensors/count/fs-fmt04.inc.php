<?php

/**
 * FS FMT / OAP — per-lane static optic catalog from OAP-C1-OEO:
 *   .6  ModeWave                    (÷100 → nm, e.g. 85000 → 850 nm multimode)
 *   .7  ModeTransmissionDistance    (÷1000 → km as in device UI, e.g. 30→0.03, 10000→10.00)
 *   .8  ModeTransmissionRate       (÷1000 → nominal Gbps class, e.g. 25500 → 25.5)
 *
 * These are essentially flat “configuration” values; graphs still allow at-a-glance comparison after swaps.
 * Same lane gating as dBm/temperature: only lanes with vSFP*State = on(1).
 *
 * If your web UI uses different units, adjust divisors here or override in LibreNMS sensor UI.
 */

echo 'FS FMT OAP count ';

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

foreach ($suites as $suiteOid => $suiteLabel) {
    foreach ($slots as $port => $slotLabel) {
        $base = '.1.3.6.1.4.1.40989.10.16.' . $suiteOid . '.' . $port;
        $laneOn = SnmpQuery::get($base . '.1.0')->value();
        if (! is_numeric($laneOn) || (int) $laneOn !== 1) {
            continue;
        }

        $prefix = str_replace('.', '-', (string) $suiteOid);

        // Wavelength (nm): SNMP is ×100
        $oidWave = $base . '.6.0';
        $wave = SnmpQuery::get($oidWave)->value();
        if (is_numeric($wave)) {
            $divWave = 100;
            $scaledWave = (float) $wave / $divWave;
            $idx = 'fmt04-lambda-' . $prefix . '-' . $port;
            discover_sensor(
                null,
                'count',
                $device,
                $oidWave,
                $idx,
                $sensor_type,
                $suiteLabel . ' ' . $slotLabel . ' wavelength (nm)',
                $divWave,
                1,
                null,
                null,
                null,
                null,
                $scaledWave,
                'snmp'
            );
        }

        // Rated reach (km): SNMP ×1000 vs UI km (30→0.03, 10000→10.00)
        $oidDist = $base . '.7.0';
        $dist = SnmpQuery::get($oidDist)->value();
        if (is_numeric($dist)) {
            $divDist = 1000;
            $scaledDist = (float) $dist / $divDist;
            $idx = 'fmt04-reach-' . $prefix . '-' . $port;
            discover_sensor(
                null,
                'count',
                $device,
                $oidDist,
                $idx,
                $sensor_type,
                $suiteLabel . ' ' . $slotLabel . ' rated reach (km)',
                $divDist,
                1,
                null,
                null,
                null,
                null,
                $scaledDist,
                'snmp'
            );
        }

        // Nominal bitrate class (÷1000 → Gbps)
        $oidRate = $base . '.8.0';
        $rate = SnmpQuery::get($oidRate)->value();
        if (is_numeric($rate)) {
            $divRate = 1000;
            $scaledRate = (float) $rate / $divRate;
            $idx = 'fmt04-nomrate-' . $prefix . '-' . $port;
            discover_sensor(
                null,
                'count',
                $device,
                $oidRate,
                $idx,
                $sensor_type,
                $suiteLabel . ' ' . $slotLabel . ' nominal rate (Gbps)',
                $divRate,
                1,
                null,
                null,
                null,
                null,
                $scaledRate,
                'snmp'
            );
        }
    }
}

// --- FMT20PA-EDFA scalars `…10.16.2.1.*` (see snmpwalk / device UI); only when module `.1.0` = on(1). ---
$edfa = '.1.3.6.1.4.1.40989.10.16.2.1';
$edfaState = SnmpQuery::get($edfa . '.1.0')->value();
if (is_numeric($edfaState) && (int) $edfaState === 1) {
    // SNMP ×100 — UI: current gain (dB), gain adjustment (dB), PUMP work/cooling current (mA).
    $edfaCounts = [
        21 => ['idx' => 'fmt04-edfa-current-gain', 'label' => 'EDFA current gain (dB)'],
        25 => ['idx' => 'fmt04-edfa-gain-adjust', 'label' => 'EDFA gain adjustment (dB)'],
        26 => ['idx' => 'fmt04-edfa-pump-work-ma', 'label' => 'EDFA PUMP work current (mA)'],
        27 => ['idx' => 'fmt04-edfa-pump-cooling-ma', 'label' => 'EDFA PUMP cooling current (mA)'],
    ];
    $divEdfa = 100;
    foreach ($edfaCounts as $subId => $meta) {
        $oid = $edfa . '.' . $subId . '.0';
        $raw = SnmpQuery::get($oid)->value();
        if (! is_numeric($raw)) {
            continue;
        }
        $scaled = (float) $raw / $divEdfa;
        discover_sensor(
            null,
            'count',
            $device,
            $oid,
            $meta['idx'],
            $sensor_type,
            $meta['label'],
            $divEdfa,
            1,
            null,
            null,
            null,
            null,
            $scaled,
            'snmp'
        );
    }
}
