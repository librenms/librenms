<?php

echo 'pCOWeb: ';

$humidities = [
    [
        'mib'       => 'CAREL-ug40cdz-MIB::roomRH.0',
        'descr'     => 'Room Relative Humidity',
        'oid'       => '.1.3.6.1.4.1.9839.2.1.2.6.0',
        'precision' => '10',
    ],
    [
        'mib'       => 'CAREL-ug40cdz-MIB::dehumPband.0',
        'descr'     => 'Dehumidification Prop. Band',
        'oid'       => '.1.3.6.1.4.1.9839.2.1.3.12.0',
        'precision' => '1',
    ],
    [
        'mib'       => 'CAREL-ug40cdz-MIB::humidPband.0',
        'descr'     => 'Humidification Prop. Band',
        'oid'       => '.1.3.6.1.4.1.9839.2.1.3.13.0',
        'precision' => '1',
    ],
    [
        'mib'       => 'CAREL-ug40cdz-MIB::dehumSetp.0',
        'descr'     => 'Dehumidification Set Point',
        'oid'       => '.1.3.6.1.4.1.9839.2.1.3.16.0',
        'precision' => '1',
    ],
    [
        'mib'       => 'CAREL-ug40cdz-MIB::humidSetp.0',
        'descr'     => 'Humidification Set Point',
        'oid'       => '.1.3.6.1.4.1.9839.2.1.3.17.0',
        'precision' => '1',
    ],
];

foreach ($humidities as $humidity) {
    $current = (snmp_get($device, $humidity['mib'], '-OqvU') / $humidity['precision']);

    $high_limit = null;
    $low_limit = null;

    if (is_numeric($current) && $current != 0) {
        $index = implode('.', array_slice(explode('.', $humidity['oid']), -5));
        discover_sensor($valid['sensor'], 'humidity', $device, $humidity['oid'], $index, 'pcoweb', $humidity['descr'], $humidity['precision'], '1', $low_limit, null, null, $high_limit, $current);
    }
}
