<?php

echo 'RFC1628 ';

$input_freq = SnmpQuery::walk('UPS-MIB::upsInputFrequency')->filterBadLines()->table()['UPS-MIB::upsInputFrequency'] ?? [];
foreach ($input_freq as $index => $data) {
    $divisor = $os->getUpsMibDivisor('UPS-MIB::upsInputFrequency');
    $descr = 'Input';
    if (count($input_freq) > 1) {
        $descr .= " Phase $index";
    }

    discover_sensor(
        $valid['sensor'],
        'frequency',
        $device,
        ".1.3.6.1.2.1.33.1.3.3.1.2.$index",
        "3.2.0.$index",
        'rfc1628',
        $descr,
        $divisor,
        current: $data / $divisor
    );
}

$output_freq = SnmpQuery::get('UPS-MIB::upsOutputFrequency.0')->value();
if (is_numeric($output_freq)) {
    $divisor = $os->getUpsMibDivisor('UPS-MIB::upsOutputFrequency');

    discover_sensor(
        $valid['sensor'],
        'frequency',
        $device,
        '.1.3.6.1.2.1.33.1.4.2.0',
        '4.2.0',
        'rfc1628',
        'Output',
        $divisor,
        current: $output_freq / $divisor
    );
}

$bypass_freq = SnmpQuery::get('UPS-MIB::upsBypassFrequency.0')->value();
if (is_numeric($bypass_freq)) {
    $divisor = $os->getUpsMibDivisor('UPS-MIB::upsBypassFrequency');

    discover_sensor(
        $valid['sensor'],
        'frequency',
        $device,
        '.1.3.6.1.2.1.33.1.5.1.0',
        '5.1.0',
        'rfc1628',
        'Bypass',
        $divisor,
        current: $bypass_freq / $divisor
    );
}

unset($input_freq, $output_freq, $bypass_freq);
