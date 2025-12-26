<?php

use LibreNMS\Util\Oid;

$raw = snmp_get($device, 'NET-SNMP-EXTEND-MIB::nsExtendOutput1Line."103"', '-Oqv', 'NET-SNMP-EXTEND-MIB');
if ($raw === false || trim((string) $raw) === '') {
    return;
}

$processes = [];
foreach (preg_split('/\s*,\s*/', trim((string) $raw)) as $entry) {
    if ($entry === '') {
        continue;
    }

    if (! preg_match('/^(.*?)\(([^)]+)\)$/', $entry, $matches)) {
        continue;
    }

    $name = trim($matches[1]);
    $status = strtoupper(trim($matches[2]));
    if ($name === '') {
        continue;
    }

    $key = strtolower(preg_replace('/[^A-Za-z0-9._-]+/', '_', $name));
    if ($key === '') {
        continue;
    }

    $processes[$key] = [
        'name' => $name,
        'status' => $status,
    ];
}

if (empty($processes)) {
    return;
}

$state_name = 'monitorappAiWafStatus';
$states = [
    ['value' => 1, 'generic' => 0, 'graph' => 1, 'descr' => 'OK'],
    ['value' => 2, 'generic' => 2, 'graph' => 1, 'descr' => 'NOT OK'],
];
create_state_index($state_name, $states);

$base_oid = Oid::of('NET-SNMP-EXTEND-MIB::nsExtendOutput1Line')->toNumeric();
$oid = $base_oid . '.' . Oid::encodeString('103')->oid;

foreach ($processes as $key => $data) {
    $status_value = $data['status'] === 'OK' ? 1 : 2;
    $index = "process.$key";
    $descr = $data['name'];

    discover_sensor(
        null,
        'state',
        $device,
        $oid,
        $index,
        $state_name,
        $descr,
        1,
        1,
        null,
        null,
        null,
        null,
        $status_value,
        'snmp',
        null,
        null,
        null,
        'Process Status'
    );
}
