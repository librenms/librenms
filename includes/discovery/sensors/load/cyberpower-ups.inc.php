<?php

echo 'CyberPower UPS ';

// "The UPS output load expressed in percentage"

$oid_array = [
    [
        'oidname'      => 'upsAdvanceOutputLoad',
        'type'        => 'cyberpower-ups',
        'index'       => 0,
        'descr'       => 'Percent',
        'divisor'     => 1,
        'mib'         => '+CPS-MIB',
    ],
];
foreach ($oid_array as $item) {
    $oids = snmp_get($device, $item['oidname'].'.'.$item['index'], '-OsqnU', $item['mib']);

    $value = explode(" ", $oids);
    $current = $value[1]/$item['divisor'];
    $current_oid = $value[0];

    if (!empty($oids)) {
        d_echo($oids);
        $oids = trim($oids);
        if ($oids) {
            echo $item['type'].' '.$item['mib'];
        }
        discover_sensor($valid['sensor'], 'load', $device, $current_oid, $current_oid, $item['type'], $item['descr'], $item['divisor'], 1, null, null, null, null, $current);
    }
}