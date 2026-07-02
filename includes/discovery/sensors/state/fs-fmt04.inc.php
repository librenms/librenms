<?php

/**
 * FS FMT / OAP — chassis PSU/fan (OAP-NMU) and per-lane SFP alarms (OAP-C1-OEO).
 */
echo 'FS FMT OAP state ';

$sensor_type = 'fs-fmt04';

// --- Chassis (same family as fs-nmu state discovery) ---
$fan = SnmpQuery::get('OAP-NMU::fanState.0')->value();
$power1 = SnmpQuery::get('OAP-NMU::power1State.0')->value();
$power2 = SnmpQuery::get('OAP-NMU::power2State.0')->value();

$oid_fan = '.1.3.6.1.4.1.40989.10.16.20.10.0';
$oid_power1 = '.1.3.6.1.4.1.40989.10.16.20.11.0';
$oid_power2 = '.1.3.6.1.4.1.40989.10.16.20.12.0';
$indexChassis = '0';

if (is_numeric($fan)) {
    $state_name = 'fs_fmt04_fanState';
    $states = [
        ['value' => 0, 'generic' => 2, 'descr' => 'off'],
        ['value' => 1, 'generic' => 0, 'descr' => 'on'],
    ];
    create_state_index($state_name, $states);

    discover_sensor(null, 'state', $device, $oid_fan, $indexChassis, $state_name, 'Fan state', 1, 1, null, null, null, null, $fan, 'snmp', $indexChassis);
}

if (is_numeric($power1)) {
    $state_name = 'fs_fmt04_power1State';
    $states = [
        ['value' => 0, 'generic' => 2, 'descr' => 'off'],
        ['value' => 1, 'generic' => 0, 'descr' => 'on'],
    ];
    create_state_index($state_name, $states);

    discover_sensor(null, 'state', $device, $oid_power1, $indexChassis, $state_name, 'Power 1 state', 1, 1, null, null, null, null, $power1, 'snmp', $indexChassis);
}

if (is_numeric($power2)) {
    $state_name = 'fs_fmt04_power2State';
    $states = [
        ['value' => 0, 'generic' => 2, 'descr' => 'off'],
        ['value' => 1, 'generic' => 0, 'descr' => 'on'],
    ];
    create_state_index($state_name, $states);

    discover_sensor(null, 'state', $device, $oid_power2, $indexChassis, $state_name, 'Power 2 state', 1, 1, null, null, null, null, $power2, 'snmp', $indexChassis);
}

// --- SFP lane alarms: 10 Tx, 11 Rx, 12 module temp (alarm / normal) ---

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

$alarmDefs = [
    10 => ['label' => 'Tx power alarm', 'suffix' => 'tx-alarm'],
    11 => ['label' => 'Rx power alarm', 'suffix' => 'rx-alarm'],
    12 => ['label' => 'module temp alarm', 'suffix' => 'temp-alarm'],
];

$state_alarm = [
    ['value' => 0, 'generic' => 2, 'descr' => 'alarm'],
    ['value' => 1, 'generic' => 0, 'descr' => 'normal'],
];

foreach ($suites as $suiteOid => $suiteLabel) {
    foreach ($slots as $port => $slotLabel) {
        $base = '.1.3.6.1.4.1.40989.10.16.' . $suiteOid . '.' . $port;
        $laneOn = SnmpQuery::get($base . '.1.0')->value();
        if (! is_numeric($laneOn) || (int) $laneOn !== 1) {
            continue;
        }

        foreach ($alarmDefs as $subId => $meta) {
            $oid = $base . '.' . $subId . '.0';
            $value = SnmpQuery::get($oid)->value();

            if (! is_numeric($value)) {
                continue;
            }

            $state_name = 'fs_fmt04_' . $meta['suffix'] . '_' . str_replace('.', 'p', (string) $suiteOid) . '_' . $port;
            create_state_index($state_name, $state_alarm);

            $descr = $suiteLabel . ' ' . $slotLabel . ' ' . $meta['label'];
            $index = 'fmt04-' . $meta['suffix'] . '-' . str_replace('.', '-', (string) $suiteOid) . '-' . $port;

            // Arg 16 is entPhysicalIndex (integer entity index), not a duplicate sensor_index — DB column is short.
            discover_sensor(null, 'state', $device, $oid, $index, $state_name, $descr, 1, 1, null, null, null, null, $value, 'snmp', null);
        }
    }
}

// --- EDFA work mode: `…2.1.8.0` (integer enum; UI AGC observed as value 3) ---
$edfa = '.1.3.6.1.4.1.40989.10.16.2.1';
$edfaOn = SnmpQuery::get($edfa . '.1.0')->value();
if (is_numeric($edfaOn) && (int) $edfaOn === 1) {
    $oidMode = $edfa . '.8.0';
    $mode = SnmpQuery::get($oidMode)->value();
    if (is_numeric($mode)) {
        $state_name = 'fs_fmt04_edfa_workmode';
        $states = [
            ['value' => 0, 'generic' => 3, 'descr' => 'mode 0'],
            ['value' => 1, 'generic' => 3, 'descr' => 'mode 1'],
            ['value' => 2, 'generic' => 3, 'descr' => 'mode 2'],
            ['value' => 3, 'generic' => 0, 'descr' => 'AGC'],
        ];
        create_state_index($state_name, $states);

        discover_sensor(
            null,
            'state',
            $device,
            $oidMode,
            'fmt04-edfa-workmode',
            $state_name,
            'EDFA work mode',
            1,
            1,
            null,
            null,
            null,
            null,
            $mode,
            'snmp',
            null
        );
    }
}
