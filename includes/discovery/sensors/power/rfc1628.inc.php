<?php

echo "RFC1628 ";

$output_power = snmpwalk_group($device, 'upsOutputPower', 'UPS-MIB');
foreach ($output_power as $index => $data) {
    $descr = 'Output';
    if (count($output_power) > 1) {
        $descr .= " Phase $index";
    }

    discover_sensor(
        $valid['sensor'],
        'power',
        $device,
        ".1.3.6.1.2.1.33.1.4.4.1.4.$index",
        300+$index,
        "rfc1628",
        $descr,
        1,
        1,
        null,
        null,
        null,
        null,
        $data['upsOutputPower']
    );
}


$input_power = snmpwalk_group($device, 'upsInputTruePower', 'UPS-MIB');
foreach ($input_power as $index => $data) {
    $descr = 'Input';
    if (count($input_power) > 1) {
        $descr .= " Phase $index";
    }

    discover_sensor(
        $valid['sensor'],
        'power',
        $device,
        ".1.3.6.1.2.1.33.1.3.3.1.5.$index",
        100+$index,
        "rfc1628",
        $descr,
        1,
        1,
        null,
        null,
        null,
        null,
        $data['upsInputTruePower']
    );
}

$bypass_power = snmpwalk_group($device, 'upsBypassPower', 'UPS-MIB');
foreach ($bypass_power as $index => $data) {
    $descr = 'Bypass';
    if (count($bypass_power) > 1) {
        $descr .= " Phase $index";
    }

    discover_sensor(
        $valid['sensor'],
        'power',
        $device,
        ".1.3.6.1.2.1.33.1.5.3.1.4.$index",
        200+$index,
        "rfc1628",
        $descr,
        1,
        1,
        null,
        null,
        null,
        null,
        $data['upsBypassPower']
    );
}

unset($output_power, $input_power, $bypass_power);
