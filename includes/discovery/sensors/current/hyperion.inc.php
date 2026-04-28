<?php

echo 'Hyperion DDMI current: ';

// DDMI tables
$ddmi = snmpwalk_cache_oid(
    $device,
    'ddmiStatusInterfaceTable',
    [],
    'MIB-DDMI',
    null,
    '-OQUs'
);

// IF-MIB ifDescr
$ifdescr = snmpwalk_cache_oid(
    $device,
    'ifDescr',
    [],
    'IF-MIB',
    null,
    '-OQUs'
);

foreach ($ddmi as $index => $entry) {

    // tylko porty z SFP
    if (($entry['ddmiStatusInterfaceA0SfpDetected'] ?? 'false') != 'true') {
        continue;
    }

    if (empty($entry['ddmiStatusInterfaceA2CurrentTxBias'])) {
        continue;
    }

    // STRING -> float
    $raw = trim($entry['ddmiStatusInterfaceA2CurrentTxBias']);
    $raw = str_replace(',', '.', $raw);

    if (!is_numeric($raw)) {
        continue;
    }

    $value = (float)$raw;

    // mapowanie DDMI -> ifIndex
    $ddmi_if = (int)$entry['ddmiStatusInterfaceIfIndex'];
    $real_ifIndex = $ddmi_if + 1000000;

    $descr = $ifdescr[$real_ifIndex]['ifDescr'] ?? "Port $ddmi_if";

    $oid_num = '.1.3.6.1.4.1.19829.1.121.1.3.2.1.1013.' . $index;

    $sensor_index = 'ddmiStatusInterfaceA2CurrentTxBias.' . $index;

    discover_sensor(
        null,
        'current',
        $device,
        $oid_num,
        $sensor_index,
        'optic_module_current',
        $descr . ' SFP current',
        100,
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
        null
    );
}
