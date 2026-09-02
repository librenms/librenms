<?php

$modio = snmpwalk_cache_oid($device, 'dinName',         [],     'MIB-MODIO', null, '-OQUs');
$modio = snmpwalk_cache_oid($device, 'dinAvailability', $modio, 'MIB-MODIO', null, '-OQUs');
$modio = snmpwalk_cache_oid($device, 'dinState',        $modio, 'MIB-MODIO', null, '-OQUs');

if (empty($modio)) {
    return;
}

$availability_map = [
    'YES' => 1,
    'NO'  => 0,
];

$state_map = [
    'LOW'  => 0,   // close
    'HIGH' => 1,   // open
];

$state_name = 'hyperion_din_state';

$states = [
    ['value' => 0, 'generic' => 3, 'graph' => 1, 'descr' => 'close'],  // close
    ['value' => 1, 'generic' => 0, 'graph' => 1, 'descr' => 'open'],   // open
];

create_state_index($state_name, $states);

foreach ($modio as $index => $entry) {

    $avail_raw = strtoupper(trim($entry['dinAvailability'] ?? ''));
    $available  = $availability_map[$avail_raw] ?? 0;

    if ($available !== 1) {
        continue;
    }

    $state_raw = strtoupper(trim($entry['dinState'] ?? ''));
    if (!array_key_exists($state_raw, $state_map)) {
        continue;
    }
    $value = $state_map[$state_raw];

    $descr = trim($entry['dinName'] ?? ('Digital Input ' . $index), '"\'');

    $oid_num      = '.1.3.6.1.4.1.19829.1.6.2.1.4.' . $index;
    $sensor_index = 'dinState.' . $index;

    discover_sensor(
        null,
        'state',
        $device,
        $oid_num,
        $sensor_index,
        $state_name,
        $descr,
        1,
        1,
        null,
        null,
        null,
        null,
        $value,
        'snmp',
        null,
        null,
        null,
        'DigitalIn'
    );
}

$state_name = 'sysutilStatusPowerSupplyState';

$states = [
    ['value' => 0, 'generic' => 0, 'graph' => 0, 'descr' => 'active'],
    ['value' => 1, 'generic' => 1, 'graph' => 0, 'descr' => 'standby'],
    ['value' => 2, 'generic' => 3, 'graph' => 0, 'descr' => 'notPresent'],
    ['value' => 3, 'generic' => 2, 'graph' => 0, 'descr' => 'fault'],
];

create_state_index($state_name, $states);

$psu_state_map = [
    'active'     => 0,
    'standby'    => 1,
    'notPresent' => 2,
    'fault'      => 3,
];
echo "DEBUG: Starting PSU discovery\n";
$psu = snmpwalk_cache_oid($device, 'sysutilStatusPowerSupplyDescription', [],   'MIB-SYSUTIL', null, '-OQUs');
$psu = snmpwalk_cache_oid($device, 'sysutilStatusPowerSupplyState',       $psu, 'MIB-SYSUTIL', null, '-OQUs');
echo "DEBUG: PSU array count: " . count($psu) . "\n";
print_r($psu);

foreach ($psu as $index => $entry) {

    $state_raw = trim($entry['sysutilStatusPowerSupplyState'] ?? '');
    $descr     = trim($entry['sysutilStatusPowerSupplyDescription'] ?? ('Power Supply ' . $index), '"\'');

    if (!isset($psu_state_map[$state_raw])) {
        continue;
    }

    $value   = $psu_state_map[$state_raw];
    $oid_num = '.1.3.6.1.4.1.19829.1.24.1.3.2.1.3.' . $index;

    discover_sensor(
        null,
        'state',
        $device,
        $oid_num,
        'psuState.' . $index,
        $state_name,
        $descr,
        1,
        1,
        null,
        null,
        null,
        null,
        $value,
        'snmp',
        null,
        null,
        null,
        'Power Supply'
    );
}