<?php

echo ' pCOWeb ';

$temperatures = [
    [
        'mib'       => 'CAREL-ug40cdz-MIB::roomTemp.0',
        'descr'     => 'Room Temperature',
        'oid'       => '.1.3.6.1.4.1.9839.2.1.2.1.0',
        'precision' => '10',
    ],
    [
        'mib'       => 'CAREL-ug40cdz-MIB::outdoorTemp.0',
        'descr'     => 'Ambient Temperature',
        'oid'       => '.1.3.6.1.4.1.9839.2.1.2.2.0',
        'precision' => '10',
    ],
    [
        'mib'       => 'CAREL-ug40cdz-MIB::deliveryTemp.0',
        'descr'     => 'Delivery Air Temperature',
        'oid'       => '.1.3.6.1.4.1.9839.2.1.2.3.0',
        'precision' => '10',
    ],
    [
        'mib'       => 'CAREL-ug40cdz-MIB::cwTemp.0',
        'descr'     => 'Chilled Water Temperature',
        'oid'       => '.1.3.6.1.4.1.9839.2.1.2.4.0',
        'precision' => '10',
    ],
    [
        'mib'       => 'CAREL-ug40cdz-MIB::hwTemp.0',
        'descr'     => 'Hot Water Temperature',
        'oid'       => '.1.3.6.1.4.1.9839.2.1.2.5.0',
        'precision' => '10',
    ],
    [
        'mib'       => 'CAREL-ug40cdz-MIB::cwoTemp.0',
        'descr'     => 'Chilled Water Outlet Temperature',
        'oid'       => '.1.3.6.1.4.1.9839.2.1.2.7.0',
        'precision' => '10',
    ],
    [
        'mib'       => 'CAREL-ug40cdz-MIB::suctTemp1.0',
        'descr'     => 'Circuit 1 Suction Temperature',
        'oid'       => '.1.3.6.1.4.1.9839.2.1.2.10.0',
        'precision' => '10',
    ],
    [
        'mib'       => 'CAREL-ug40cdz-MIB::suctTemp2.0',
        'descr'     => 'Circuit 2 Suction Temperature',
        'oid'       => '.1.3.6.1.4.1.9839.2.1.2.11.0',
        'precision' => '10',
    ],
    [
        'mib'       => 'CAREL-ug40cdz-MIB::evapTemp1.0',
        'descr'     => 'Circuit 1 Evap. Temperature',
        'oid'       => '.1.3.6.1.4.1.9839.2.1.2.12.0',
        'precision' => '10',
    ],
    [
        'mib'       => 'CAREL-ug40cdz-MIB::evapTemp2.0',
        'descr'     => 'Circuit 2 Evap. Temperature',
        'oid'       => '.1.3.6.1.4.1.9839.2.1.2.13.0',
        'precision' => '10',
    ],
    [
        'mib'       => 'CAREL-ug40cdz-MIB::ssh1.0',
        'descr'     => 'Circuit 1 Superheat',
        'oid'       => '.1.3.6.1.4.1.9839.2.1.2.14.0',
        'precision' => '10',
    ],
    [
        'mib'       => 'CAREL-ug40cdz-MIB::ssh2.0',
        'descr'     => 'Circuit 2 Superheat',
        'oid'       => '.1.3.6.1.4.1.9839.2.1.2.15.0',
        'precision' => '10',
    ],
    [
        'mib'       => 'CAREL-ug40cdz-MIB::coolSetP.0',
        'descr'     => 'Cooling Set Point',
        'oid'       => '.1.3.6.1.4.1.9839.2.1.2.20.0',
        'precision' => '10',
    ],
    [
        'mib'       => 'CAREL-ug40cdz-MIB::coolDiff.0',
        'descr'     => 'Cooling Prop. Band',
        'oid'       => '.1.3.6.1.4.1.9839.2.1.2.21.0',
        'precision' => '10',
    ],
    [
        'mib'       => 'CAREL-ug40cdz-MIB::cool2SetP.0',
        'descr'     => 'Cooling 2nd Set Point',
        'oid'       => '.1.3.6.1.4.1.9839.2.1.2.22.0',
        'precision' => '10',
    ],
    [
        'mib'       => 'CAREL-ug40cdz-MIB::heatSetP.0',
        'descr'     => 'Heating Set Point',
        'oid'       => '.1.3.6.1.4.1.9839.2.1.2.23.0',
        'precision' => '10',
    ],
    [
        'mib'       => 'CAREL-ug40cdz-MIB::heatDiff.0',
        'descr'     => 'Heating Prop. Band',
        'oid'       => '.1.3.6.1.4.1.9839.2.1.2.25.0',
        'precision' => '10',
    ],
    [
        'mib'       => 'CAREL-ug40cdz-MIB::heat2SetP.0',
        'descr'     => 'Heating 2nd Set Point',
        'oid'       => '.1.3.6.1.4.1.9839.2.1.2.24.0',
        'precision' => '10',
    ],
];

foreach ($temperatures as $temperature) {
    $current = (snmp_get($device, $temperature['mib'], '-OqvU') / $temperature['precision']);

    $high_limit = null;
    $low_limit = null;

    if (is_numeric($current) && $current != 0) {
        $index = implode('.', array_slice(explode('.', $temperature['oid']), -5));
        discover_sensor($valid['sensor'], 'temperature', $device, $temperature['oid'], $index, 'pcoweb', $temperature['descr'], $temperature['precision'], '1', $low_limit, null, null, $high_limit, $current);
    }
}
