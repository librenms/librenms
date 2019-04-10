<?php

echo 'Cyberpower UPS ';

//Currently only supports single-phase devices

$oid_array = [
    [
        'oidname'     => 'upsAdvanceOutputVoltage',
        'type'        => 'cyberpower-ups',
        'index'       => 0,
        'descr'       => 'Output',
        'divisor'     => 10,
        'mib'         => '+CPS-MIB',
    ],
    [
        'oidname'     => 'upsAdvanceInputLineVoltage',
        'type'        => 'cyberpower-ups',
        'index'       => 0,
        'descr'       => 'Input',
        'divisor'     => 10,
        'mib'         => '+CPS-MIB',
    ],
];
foreach ($oid_array as $item) {
    $oids = snmp_get($device, $item['oidname'].'.'.$item['index'], '-OsqnU', $item['mib']);
    d_echo($oids."\n");

    $value = explode(" ", $oids);
    $current = $value[1]/$item['divisor'];
    $current_oid = $value[0];

    if (!empty($oids)) {
        d_echo($oids);
        $oids = trim($oids);
        if ($oids) {
            echo $item['type'].' '.$item['mib'];
        }
        discover_sensor($valid['sensor'], 'voltage', $device, $current_oid, $current_oid, $item['type'], $item['descr'], $item['divisor'], 1, null, null, null, null, $current);
    }
}
