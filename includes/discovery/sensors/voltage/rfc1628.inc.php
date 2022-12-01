<?php

echo 'RFC1628 ';

$battery_volts = SnmpQuery::get('UPS-MIB::upsBatteryVoltage.0')->value();
if (is_numeric($battery_volts)) {
    $divisor = $os->getUpsMibDivisor('UPS-MIB::upsBatteryVoltage');

    discover_sensor(
        $valid['sensor'],
        'voltage',
        $device,
        '.1.3.6.1.2.1.33.1.2.5.0',
        '1.2.5.0',
        'rfc1628',
        'Battery',
        $divisor,
        current: $battery_volts / $divisor
    );
}

$output_volts = SnmpQuery::walk('UPS-MIB::upsOutputVoltage')->filterBadLines()->table()['UPS-MIB::upsOutputVoltage'] ?? [];
foreach ($output_volts as $index => $data) {
    $divisor = $os->getUpsMibDivisor('UPS-MIB::upsOutputVoltage');
    $descr = 'Output';
    if (count($output_volts) > 1) {
        $descr .= " Phase $index";
    }

    discover_sensor(
        $valid['sensor'],
        'voltage',
        $device,
        ".1.3.6.1.2.1.33.1.4.4.1.2.$index",
        $index,
        'rfc1628',
        $descr,
        $divisor,
        current: $data / $divisor
    );
}

$input_volts = SnmpQuery::walk('UPS-MIB::upsInputVoltage')->filterBadLines()->table()['UPS-MIB::upsInputVoltage'] ?? [];
foreach ($input_volts as $index => $data) {
    $divisor = $os->getUpsMibDivisor('UPS-MIB::upsInputVoltage');
    $descr = 'Input';
    if (count($input_volts) > 1) {
        $descr .= " Phase $index";
    }

    discover_sensor(
        $valid['sensor'],
        'voltage',
        $device,
        ".1.3.6.1.2.1.33.1.3.3.1.3.$index",
        100 + $index,
        'rfc1628',
        $descr,
        $divisor,
        current: $data / $divisor
    );
}

$bypass_volts = SnmpQuery::walk('UPS-MIB::upsBypassVoltage')->filterBadLines()->table()['UPS-MIB::upsBypassVoltage'] ?? [];
foreach ($bypass_volts as $index => $data) {
    $divisor = $os->getUpsMibDivisor('UPS-MIB::upsBypassVoltage');
    $descr = 'Bypass';
    if (count($bypass_volts) > 1) {
        $descr .= " Phase $index";
    }

    discover_sensor(
        $valid['sensor'],
        'voltage',
        $device,
        ".1.3.6.1.2.1.33.1.5.3.1.2.$index",
        200 + $index,
        'rfc1628',
        $descr,
        $divisor,
        current: $data / $divisor
    );
}

unset($input_volts, $output_volts, $battery_volts, $bypass_volts);
