<?php

$voltages = [
    1 => ['oid' => '.1.3.6.1.4.1.45621.3.3.15.0', 'mib' => 'TPDIN3-MIB::v1int.0', 'index' => 'v1int.0'],
    2 => ['oid' => '.1.3.6.1.4.1.45621.3.3.16.0', 'mib' => 'TPDIN3-MIB::v2int.0', 'index' => 'v2int.0'],
    3 => ['oid' => '.1.3.6.1.4.1.45621.3.3.17.0', 'mib' => 'TPDIN3-MIB::v3int.0', 'index' => 'v3int.0'],
    4 => ['oid' => '.1.3.6.1.4.1.45621.3.3.18.0', 'mib' => 'TPDIN3-MIB::v4int.0', 'index' => 'v4int.0'],
];

foreach ($voltages as $num => $config) {
    $current = SnmpQuery::get($config['mib'])->value();

    if (is_numeric($current)) {
        discover_sensor(
            null,
            'voltage',
            $device,
            $config['oid'],
            $config['index'],
            'tpdin3',
            "voltage $num",
            10,
            1,
            null,
            null,
            null,
            null,
            $current
        );
    }
}
