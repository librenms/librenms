<?php

echo 'RFC1628 ';

$output_power = snmpwalk_group($device, 'upsOutputPower', 'UPS-MIB');
foreach ($output_power as $index => $data) {
    $pwr_oid = ".1.3.6.1.2.1.33.1.4.4.1.4.$index";
    $descr = 'Output';
    if (count($output_power) > 1) {
        $descr .= " Phase $index";
    }
    if (is_array($data['upsOutputPower'])) {
        $data['upsOutputPower'] = $data['upsOutputPower'][0];
        $pwr_oid .= '.0';
    }

    discover_sensor(
        $valid['sensor'],
        'power',
        $device,
        $pwr_oid,
        300 + $index,
        'rfc1628',
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
    $pwr_oid = ".1.3.6.1.2.1.33.1.3.3.1.5.$index";
    $descr = 'Input';
    if (count($input_power) > 1) {
        $descr .= " Phase $index";
    }
    if (is_array($data['upsInputTruePower'])) {
        $data['upsInputTruePower'] = $data['upsInputTruePower'][0];
        $pwr_oid .= '.0';
    }

    discover_sensor(
        $valid['sensor'],
        'power',
        $device,
        $pwr_oid,
        100 + $index,
        'rfc1628',
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
    $pwr_oid = ".1.3.6.1.2.1.33.1.5.3.1.4.$index";
    $descr = 'Bypass';
    if (count($bypass_power) > 1) {
        $descr .= " Phase $index";
    }
    if (is_array($data['upsBypassPower'])) {
        $data['upsBypassPower'] = $data['upsBypassPower'][0];
        $pwr_oid .= '.0';
    }

    discover_sensor(
        $valid['sensor'],
        'power',
        $device,
        $pwr_oid,
        200 + $index,
        'rfc1628',
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
