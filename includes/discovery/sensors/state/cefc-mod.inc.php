<?php
// Discover module state sensors via CISCO-ENTITY-FRU-CONTROL-MIB
// Index comes from ENTITY-MIB entPhysicalTable
// Oper status: cefcModuleOperStatus (.1.3.6.1.4.1.9.9.117.1.2.1.1.2.<entPhysicalIndex>)
// Descr from entPhysicalDescr (.1.3.6.1.2.1.47.1.1.1.1.2.<idx>)

if ($device['os'] !== 'cisco-ucs-fi') {
    return;
}

$descrs = \SnmpQuery::walk('ENTITY-MIB::entPhysicalDescr')->table(1);
$opers  = \SnmpQuery::numeric()->walk('1.3.6.1.4.1.9.9.117.1.2.1.1.2')->table(1);

if (!is_array($opers) || empty($opers)) {
    return;
}

$state_name = 'cefcModuleOperStatus';
$states = [
    ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'ok'],
    ['value' => 2, 'generic' => 3, 'graph' => 0, 'descr' => 'unknown'],
    ['value' => 3, 'generic' => 1, 'graph' => 0, 'descr' => 'okButDiagFailed'],
    ['value' => 4, 'generic' => 1, 'graph' => 0, 'descr' => 'boot'],
    ['value' => 5, 'generic' => 1, 'graph' => 0, 'descr' => 'selfTest'],
    ['value' => 6, 'generic' => 2, 'graph' => 0, 'descr' => 'failed'],
    ['value' => 7, 'generic' => 1, 'graph' => 0, 'descr' => 'missing'],
    ['value' => 8, 'generic' => 2, 'graph' => 0, 'descr' => 'mismatchWithParent'],
    ['value' => 9, 'generic' => 2, 'graph' => 0, 'descr' => 'mismatchConfig'],
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
}
