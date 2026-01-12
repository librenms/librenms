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
    ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'operable'],       // OK
    ['value' => 2, 'generic' => 2, 'graph' => 0, 'descr' => 'inoperable'],     // CRIT
    ['value' => 3, 'generic' => 1, 'graph' => 0, 'descr' => 'degraded'],       // WARN
    ['value' => 4, 'generic' => 1, 'graph' => 0, 'descr' => 'poweredOff'],     // WARN
    ['value' => 5, 'generic' => 2, 'graph' => 0, 'descr' => 'powerProblem'],   // CRIT
    ['value' => 6, 'generic' => 1, 'graph' => 0, 'descr' => 'removed'],        // WARN (often notPresent)
    ['value' => 7, 'generic' => 2, 'graph' => 0, 'descr' => 'voltageProblem'], // CRIT
    ['value' => 8, 'generic' => 2, 'graph' => 0, 'descr' => 'thermalProblem'], // CRIT
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
}
