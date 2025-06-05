<?php

$currents = [
    1 => ['oid' => '.1.3.6.1.4.1.45621.3.3.19.0', 'mib' => 'TPDIN3-MIB::i1int.0', 'index' => 'i1int.0'],
    2 => ['oid' => '.1.3.6.1.4.1.45621.3.3.20.0', 'mib' => 'TPDIN3-MIB::i2int.0', 'index' => 'i2int.0'],
    3 => ['oid' => '.1.3.6.1.4.1.45621.3.3.21.0', 'mib' => 'TPDIN3-MIB::i3int.0', 'index' => 'i3int.0'],
    4 => ['oid' => '.1.3.6.1.4.1.45621.3.3.22.0', 'mib' => 'TPDIN3-MIB::i4int.0', 'index' => 'i4int.0'],
];

foreach ($currents as $num => $config) {
    $current = SnmpQuery::get($config['mib'])->value();

    if (is_numeric($current)) {
        discover_sensor(
            null,
            'current',
            $device,
            $config['oid'],
            $config['index'],
            'tpdin3',
            "current $num",
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
