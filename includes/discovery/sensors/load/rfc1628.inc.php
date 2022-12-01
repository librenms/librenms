<?php

echo 'RFC1628 ';

$load_data = SnmpQuery::walk('UPS-MIB::upsOutputPercentLoad')->filterBadLines()->table()['UPS-MIB::upsOutputPercentLoad'] ?? [];

foreach ($load_data as $index => $data) {
    $divisor = $os->getUpsMibDivisor('UPS-MIB::upsOutputPercentLoad');
    $descr = 'Percentage load';
    if (count($load_data) > 1) {
        $descr .= " $index";
    }

    discover_sensor(
        $valid['sensor'],
        'load',
        $device,
        ".1.3.6.1.2.1.33.1.4.4.1.5.$index",
        500 + $index,
        'rfc1628',
        $descr,
        $divisor,
        current: $data / $divisor
    );
}
