<?php

if ($device['os'] !== 'alteonos') {
    return;
}

echo 'Alteon ';

$tempSensors = [
    [
        'oid' => 'ALTEON-CHEETAH-SWITCH-MIB::hwTemperatureSensor1.0',
        'num_oid' => '.1.3.6.1.4.1.1872.2.5.1.3.1.22.0',
        'index' => '1',
        'descr' => 'Chassis Temperature Sensor 1',
    ],
    [
        'oid' => 'ALTEON-CHEETAH-SWITCH-MIB::hwTemperatureSensor2.0',
        'num_oid' => '.1.3.6.1.4.1.1872.2.5.1.3.1.23.0',
        'index' => '2',
        'descr' => 'Chassis Temperature Sensor 2',
    ],
];

foreach ($tempSensors as $sensor) {
    $response = \SnmpQuery::get($sensor['oid']);
    if (! $response->isValid()) {
        continue;
    }

    $value = $response->value();
    if (! preg_match('/-?\d+(\.\d+)?/', (string) $value, $matches)) {
        continue;
    }

    $current = (float) $matches[0];
    discover_sensor(null, 'temperature', $device, $sensor['num_oid'], 'alteonHwTemp.' . $sensor['index'], 'alteon-hw-temp', $sensor['descr'], 1, 1, null, null, null, null, $current);
}
