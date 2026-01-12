<?php
// Discover module state sensors via CISCO-ENTITY-FRU-CONTROL-MIB
// Index comes from ENTITY-MIB entPhysicalTable
// Oper status: cefcModuleOperStatus (.1.3.6.1.4.1.9.9.117.1.2.1.1.2.<entPhysicalIndex>)
// Descr from entPhysicalDescr (.1.3.6.1.2.1.47.1.1.1.1.2.<idx>)

if ($device['os'] !== 'cisco-ucs-fi') {
    return;
}

$descrs = snmpwalk_cache_oid($device, 'entPhysicalDescr', [], 'ENTITY-MIB');
$opers  = snmpwalk_cache_oid($device, '1.3.6.1.4.1.9.9.117.1.2.1.1.2', [], null, null);

if (!is_array($opers) || empty($opers)) {
    return;
}

$state_name = 'cefcModuleOperStatus';
$states = [
    1 => ['ok',                  0],
    2 => ['unknown',             3],
    3 => ['okButDiagFailed',     1],
    4 => ['boot',                1],
    5 => ['selfTest',            1],
    6 => ['failed',              2],
    7 => ['missing',             1],
    8 => ['mismatchWithParent',  2],
    9 => ['mismatchConfig',      2],
];
create_state_index($state_name, $states);

foreach ($opers as $idx => $row) {
    $value = $row[array_key_first($row)] ?? null;
    if (!is_numeric($value)) {
        continue;
    }
    $d = $descrs[$idx]['entPhysicalDescr'] ?? "Module $idx";

    discover_sensor(
        $valid['sensor'],
        'state',
        $device,
        "1.3.6.1.4.1.9.9.117.1.2.1.1.2.$idx",
        $idx,
        $state_name,
        $d,
        1,
        1,
        null,
        null,
        null,
        null,
        $value
    );

    $sensor_id = dbFetchCell('SELECT `sensor_id` FROM `sensors` WHERE `sensor_class`=? AND `sensor_index`=? AND `device_id`=?', ['state', $idx, $device['device_id']]);
    if ($sensor_id) {
        foreach ($states as $k => $s) {
            set_state_index($sensor_id, $state_name, $k, $s[0], $s[1]);
        }
    }
}
