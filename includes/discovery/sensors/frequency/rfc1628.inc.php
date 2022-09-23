<?php

echo 'RFC1628 ';

$input_freq = snmpwalk_group($device, 'upsInputFrequency', 'UPS-MIB');
foreach ($input_freq as $index => $data) {
    $freq_oid = ".1.3.6.1.2.1.33.1.3.3.1.2.$index";
    $divisor = get_device_divisor($device, $pre_cache['poweralert_serial'], 'frequency', $freq_oid);
    $descr = 'Input';
    if (count($input_freq) > 1) {
        $descr .= " Phase $index";
    }
    if (is_array($data['upsInputFrequency'])) {
        $data['upsInputFrequency'] = $data['upsInputFrequency'][0];
        $freq_oid .= '.0';
    }

    discover_sensor(
        $valid['sensor'],
        'frequency',
        $device,
        $freq_oid,
        "3.2.0.$index",
        'rfc1628',
        $descr,
        $divisor,
        1,
        null,
        null,
        null,
        null,
        $data['upsInputFrequency'] / $divisor
    );
}

$output_freq = snmp_get($device, 'upsOutputFrequency.0', '-OqvU', 'UPS-MIB');
if (is_numeric($output_freq)) {
    $freq_oid = '.1.3.6.1.2.1.33.1.4.2.0';
    $divisor = get_device_divisor($device, $pre_cache['poweralert_serial'], 'frequency', $freq_oid);

    discover_sensor(
        $valid['sensor'],
        'frequency',
        $device,
        $freq_oid,
        '4.2.0',
        'rfc1628',
        'Output',
        $divisor,
        1,
        null,
        null,
        null,
        null,
        $output_freq / $divisor
    );
}

$bypass_freq = snmp_get($device, 'upsBypassFrequency.0', '-OqvU', 'UPS-MIB');
if (is_numeric($bypass_freq)) {
    $freq_oid = '.1.3.6.1.2.1.33.1.5.1.0';
    $divisor = get_device_divisor($device, $pre_cache['poweralert_serial'], 'frequency', $freq_oid);

    discover_sensor(
        $valid['sensor'],
        'frequency',
        $device,
        $freq_oid,
        '5.1.0',
        'rfc1628',
        'Bypass',
        $divisor,
        1,
        null,
        null,
        null,
        null,
        $bypass_freq / $divisor
    );
}

unset($input_freq, $output_freq, $bypass_freq);
