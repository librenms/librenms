<?php
// Discover PSU state sensors on UCS FI using CISCO-UNIFIED-COMPUTING-EQUIPMENT-MIB
// Table: cucsEquipmentPsuTable
// DN:      1.3.6.1.4.1.9.9.719.1.15.56.1.2  (cucsEquipmentPsuDn)         -> "sys/switch-A/psu-1"
// OperState: 1.3.6.1.4.1.9.9.719.1.15.56.1.7 (cucsEquipmentPsuOperState)  -> integer

if ($device['os'] !== 'cisco-ucs-fi') {
    return;
}

$psu_dn   = snmpwalk_cache_oid($device, '1.3.6.1.4.1.9.9.719.1.15.56.1.2', [], null, null);
$psu_stat = snmpwalk_cache_oid($device, '1.3.6.1.4.1.9.9.719.1.15.56.1.7', [], null, null);

if (!is_array($psu_dn) || !is_array($psu_stat)) {
    return;
}

$state_name = 'cucsEquipmentPsuOperState';
$states = [
    // value => [descr, generic_severity]
    1 => ['operable',       0], // OK
    2 => ['inoperable',     2], // CRIT
    3 => ['degraded',       1], // WARN
    4 => ['poweredOff',     1], // WARN
    5 => ['powerProblem',   2], // CRIT
    6 => ['removed',        1], // WARN (often notPresent)
    7 => ['voltageProblem', 2], // CRIT
    8 => ['thermalProblem', 2], // CRIT
];

// register state mapping (creates/updates state translation once)
create_state_index($state_name, $states);

foreach ($psu_stat as $index => $row) {
    $value = $row[array_key_first($row)] ?? null;
    if (!is_numeric($value)) {
        continue;
    }

    $dnRow = $psu_dn[$index] ?? null;
    $dn    = is_array($dnRow) ? reset($dnRow) : (is_string($dnRow) ? $dnRow : null);
    $descr = $dn ?: "UCS PSU $index"; // e.g., "sys/switch-A/psu-1"

    // Sensortype "state" with our named state index
    discover_sensor(
        $valid['sensor'],
        'state',
        $device,
        "1.3.6.1.4.1.9.9.719.1.15.56.1.7.$index",
        $index,
        $state_name,
        $descr,
        1,
        1,
        null,
        null,
        null,
        null,
        $value
    );

    // Tie the sensor to our state translation
    // (LibreNMS helper: maps sensor_id -> state index & values)
    $sensor_id = dbFetchCell('SELECT `sensor_id` FROM `sensors` WHERE `sensor_class`=? AND `sensor_index`=? AND `device_id`=?', ['state', $index, $device['device_id']]);
    if ($sensor_id) {
        foreach ($states as $k => $s) {
            set_state_index($sensor_id, $state_name, $k, $s[0], $s[1]);
        }
    }
}
