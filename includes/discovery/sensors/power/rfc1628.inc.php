<?php

echo 'RFC1628 ';

$output_power = SnmpQuery::walk('UPS-MIB::upsOutputPower')->filterBadLines()->table()['UPS-MIB::upsOutputPower'] ?? [];
foreach ($output_power as $index => $data) {
    $divisor = $os->getUpsMibDivisor('UPS-MIB::upsOutputPower');
    $descr = 'Output';
    if (count($output_power) > 1) {
        $descr .= " Phase $index";
    }

    discover_sensor(
        $valid['sensor'],
        'power',
        $device,
        ".1.3.6.1.2.1.33.1.4.4.1.4.$index",
        300 + $index,
        'rfc1628',
        $descr,
        $divisor,
        1,
        null,
        null,
        null,
        null,
        $data / $divisor
    );
}

$input_power = SnmpQuery::walk('UPS-MIB::upsInputTruePower')->filterBadLines()->table()['UPS-MIB::upsInputTruePower'] ?? [];
foreach ($input_power as $index => $data) {
    $divisor = $os->getUpsMibDivisor('UPS-MIB::upsInputTruePower');
    $descr = 'Input';
    if (count($input_power) > 1) {
        $descr .= " Phase $index";
    }
    $divisor = $os->getUpsMibDivisor('upsInputTruePower');

    discover_sensor(
        $valid['sensor'],
        'power',
        $device,
        ".1.3.6.1.2.1.33.1.3.3.1.5.$index",
        100 + $index,
        'rfc1628',
        $descr,
        $divisor,
        current: $data / $divisor
    );
}

$bypass_power = SnmpQuery::walk('UPS-MIB::upsBypassPower')->filterBadLines()->table()['UPS-MIB::upsBypassPower'] ?? [];
foreach ($bypass_power as $index => $data) {
    $divisor = $os->getUpsMibDivisor('UPS-MIB::upsBypassPower');
    $descr = 'Bypass';
    if (count($bypass_power) > 1) {
        $descr .= " Phase $index";
    }

    discover_sensor(
        $valid['sensor'],
        'power',
        $device,
        ".1.3.6.1.2.1.33.1.5.3.1.4.$index",
        200 + $index,
        'rfc1628',
        $descr,
        $divisor,
        current: $data / $divisor
    );
}

unset($output_power, $input_power, $bypass_power);
