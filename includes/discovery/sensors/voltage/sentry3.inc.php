<?php

$oids = snmpwalk_group($device, 'infeedVoltage', 'Sentry3-MIB', 2);
d_echo($oids);

foreach ($oids as $index => $first) {
    foreach ($first as $end => $data) {
        $divisor = 10;
        discover_sensor(
            $valid['sensor'],
            'voltage',
            $device,
            ".1.3.6.1.4.1.1718.3.2.2.1.11.$index.$end",
            $index,
            'sentry3',
            'Tower ' . $index,
            $divisor,
            1,
            null,
            null,
            null,
            null,
            empty($data['infeedVoltage']) ? 0 : ($data['infeedVoltage'] / $divisor)
        );
    }
}
