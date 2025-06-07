<?php

$currents = [
    1 => ['oid' => '.1.3.6.1.4.1.45621.3.3.23.0', 'mib' => 'TPDIN3-MIB::t1int.0', 'index' => 't1int.0'],
    2 => ['oid' => '.1.3.6.1.4.1.45621.3.3.24.0', 'mib' => 'TPDIN3-MIB::t2int.0', 'index' => 't2int.0'],
];

foreach ($currents as $num => $config) {
    $current = SnmpQuery::get($config['mib'])->value();

    if (is_numeric($current)) {
        discover_sensor(
            null,
            'temperature',
            $device,
            $config['oid'],
            $config['index'],
            'tpdin3',
            "temperature $num",
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
