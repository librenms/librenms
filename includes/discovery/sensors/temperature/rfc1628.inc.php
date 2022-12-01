<?php

echo 'RFC1628 ';

$battery_temp = SnmpQuery::get('UPS-MIB::upsBatteryTemperature.0')->value();
if (is_numeric($battery_temp)) {
    $divisor = $os->getUpsMibDivisor('UPS-MIB::upsBatteryTemperature');
    discover_sensor(
        $valid['sensor'],
        'temperature',
        $device,
        '.1.3.6.1.2.1.33.1.2.7.0',
        0,
        'rfc1628',
        'Battery',
        $divisor,
        current: $battery_temp / $divisor
    );
}

unset($battery_temp);
