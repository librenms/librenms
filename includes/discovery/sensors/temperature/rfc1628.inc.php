<?php

echo 'RFC1628 ';

$battery_temp = SnmpQuery::get('UPS-MIB::upsBatteryTemperature.0')->value();
if (is_numeric($battery_temp)) {
    discover_sensor(
        $valid['sensor'],
        'temperature',
        $device,
        '.1.3.6.1.2.1.33.1.2.7.0',
        0,
        'rfc1628',
        'Battery',
        1,
        1,
        null,
        null,
        null,
        null,
        $battery_temp
    );
}

unset($battery_temp);
