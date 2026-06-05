<?php

/**
 * FS FMT / OAP — optical power per SFP lane (OEO suites 1.2 and 3.2).
 *
 * OIDs follow OAP-C1-OEO (LibreNMS mibs/fs/OAP-C1-OEO). Raw Tx/Rx are dBm×100 (same as fs-nmu).
 * Lane state: sub-id .1 (vSFP*State) off(0) / on(1) — skip Tx/Rx when off so empty slots stay clean.
 * EDFA block `…10.16.2.1` (FMT20PA-EDFA): included when `.1.0` = on — see tail of this file.
 *
 * @author   OP-16485 / FS FMT04-CH1U integration
 * @license  GPLv3
 */

echo 'FS FMT OAP dBm ';

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

$divisor = 100;
$multiplier = 1;
$sensor_type = 'fs-fmt04';

foreach ($suites as $suiteOid => $suiteLabel) {
    foreach ($slots as $port => $slotLabel) {
        $base = '.1.3.6.1.4.1.40989.10.16.' . $suiteOid . '.' . $port;

        $stateOid = $base . '.1.0';
        $laneOn = SnmpQuery::get($stateOid)->value();
        if (! is_numeric($laneOn) || (int) $laneOn !== 1) {
            continue;
        }

        $oidTx = $base . '.4.0';
        $oidRx = $base . '.5.0';

        $tx = SnmpQuery::get($oidTx)->value();
        $rx = SnmpQuery::get($oidRx)->value();

        $indexPrefix = 'fmt04-tx-' . str_replace('.', '-', (string) $suiteOid) . '-' . $port;

        if (is_numeric($tx)) {
            $descr = $suiteLabel . ' ' . $slotLabel . ' Tx Power';
            $scaledTx = (float) $tx / $divisor;
            discover_sensor(null, 'dbm', $device, $oidTx, $indexPrefix, $sensor_type, $descr, $divisor, $multiplier, null, null, null, null, $scaledTx, 'snmp');
        }

        if (is_numeric($rx)) {
            $descr = $suiteLabel . ' ' . $slotLabel . ' Rx Power';
            $indexRx = 'fmt04-rx-' . str_replace('.', '-', (string) $suiteOid) . '-' . $port;
            $scaledRx = (float) $rx / $divisor;
            discover_sensor(null, 'dbm', $device, $oidRx, $indexRx, $sensor_type, $descr, $divisor, $multiplier, null, null, null, null, $scaledRx, 'snmp');
        }
    }
}

// --- FMT20PA-EDFA (enterprise `…10.16.2.1.*` from snmpwalk; not in OAP-C1-OEO card1 MIB text) ---
// .1 = module on(1) / off(0); .24 PUMP optical, .28/.29 input/output — dBm×100 (same scaling as OEO Tx/Rx).
$edfa = '.1.3.6.1.4.1.40989.10.16.2.1';
$edfaState = SnmpQuery::get($edfa . '.1.0')->value();
if (is_numeric($edfaState) && (int) $edfaState === 1) {
    $edfaMetrics = [
        24 => 'EDFA PUMP optical power (dBm)',
        28 => 'EDFA optical input (dBm)',
        29 => 'EDFA optical output (dBm)',
    ];
    foreach ($edfaMetrics as $subId => $label) {
        $oid = $edfa . '.' . $subId . '.0';
        $raw = SnmpQuery::get($oid)->value();
        if (! is_numeric($raw)) {
            continue;
        }
        $scaled = (float) $raw / $divisor;
        $index = 'fmt04-edfa-dbm-' . $subId;
        discover_sensor(null, 'dbm', $device, $oid, $index, $sensor_type, $label, $divisor, $multiplier, null, null, null, null, $scaled, 'snmp');
    }
}
