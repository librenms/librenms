<?php

echo 'RFC1628 ';

$battery_current = SnmpQuery::get('UPS-MIB::upsBatteryCurrent.0')->value();

if (is_numeric($battery_current)) {
    $oid = '.1.3.6.1.2.1.33.1.2.6.0';
    $divisor = $os->getUpsMibDivisor('UPS-MIB::upsBatteryCurrent');

    discover_sensor(
        $valid['sensor'],
        'current',
        $device,
        $oid,
        500,
        'rfc1628',
        'Battery',
        $divisor,
        1,
        null,
        null,
        null,
        null,
        $battery_current / $divisor
    );
}

$output_current = SnmpQuery::walk('UPS-MIB::upsOutputCurrent')->filterBadLines()->table()['UPS-MIB::upsOutputCurrent'] ?? [];
foreach ($output_current as $index => $data) {
    $divisor = $os->getUpsMibDivisor('UPS-MIB::upsOutputCurrent');
    $descr = 'Output';
    if (count($output_current) > 1) {
        $descr .= " Phase $index";
    }

    discover_sensor(
        $valid['sensor'],
        'current',
        $device,
        ".1.3.6.1.2.1.33.1.4.4.1.3.$index",
        $index,
        'rfc1628',
        $descr,
        $divisor,
        current: $data / $divisor
    );
}

$input_current = SnmpQuery::walk('UPS-MIB::upsInputCurrent')->filterBadLines()->table()['UPS-MIB::upsInputCurrent'] ?? [];
foreach ($input_current as $index => $data) {
    $divisor = $os->getUpsMibDivisor('UPS-MIB::upsInputCurrent');
    $descr = 'Input';
    if (count($input_current) > 1) {
        $descr .= " Phase $index";
    }

    discover_sensor(
        $valid['sensor'],
        'current',
        $device,
        ".1.3.6.1.2.1.33.1.3.3.1.4.$index",
        100 + $index,
        'rfc1628',
        $descr,
        $divisor,
        current: $data / $divisor
    );
}

$bypass_current = SnmpQuery::walk('UPS-MIB::upsBypassCurrent')->filterBadLines()->table()['UPS-MIB::upsBypassCurrent'] ?? [];
foreach ($bypass_current as $index => $data) {
    $divisor = $os->getUpsMibDivisor('UPS-MIB::upsBypassCurrent');
    $descr = 'Bypass';
    if (count($bypass_current) > 1) {
        $descr .= " Phase $index";
    }

    discover_sensor(
        $valid['sensor'],
        'current',
        $device,
        ".1.3.6.1.2.1.33.1.5.3.1.3.$index",
        200 + $index,
        'rfc1628',
        $descr,
        $divisor,
        current: $data / $divisor
    );
}

unset($battery_current, $output_current, $input_current, $bypass_current);
