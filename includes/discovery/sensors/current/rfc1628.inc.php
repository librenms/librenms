<?php

echo 'RFC1628 ';

$battery_current = snmp_get($device, 'upsBatteryCurrent.0', '-OqvU', 'UPS-MIB');

if (is_numeric($battery_current)) {
    $oid = '.1.3.6.1.2.1.33.1.2.6.0';
    $divisor = get_device_divisor($device, $pre_cache['poweralert_serial'], 'current', $oid);

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

$output_current = snmpwalk_group($device, 'upsOutputCurrent', 'UPS-MIB');
foreach ($output_current as $index => $data) {
    $oid = ".1.3.6.1.2.1.33.1.4.4.1.3.$index";
    $divisor = get_device_divisor($device, $pre_cache['poweralert_serial'], 'current', $oid);
    $descr = 'Output';
    if (count($output_current) > 1) {
        $descr .= " Phase $index";
    }
    if (is_array($data['upsOutputCurrent'])) {
        $data['upsOutputCurrent'] = $data['upsOutputCurrent'][0];
        $oid .= '.0';
    }

    discover_sensor(
        $valid['sensor'],
        'current',
        $device,
        $oid,
        $index,
        'rfc1628',
        $descr,
        $divisor,
        1,
        null,
        null,
        null,
        null,
        $data['upsOutputCurrent'] / $divisor
    );
}

$input_current = snmpwalk_group($device, 'upsInputCurrent', 'UPS-MIB');
foreach ($input_current as $index => $data) {
    $oid = ".1.3.6.1.2.1.33.1.3.3.1.4.$index";
    $divisor = get_device_divisor($device, $pre_cache['poweralert_serial'], 'current', $oid);
    $descr = 'Input';
    if (count($input_current) > 1) {
        $descr .= " Phase $index";
    }
    if (is_array($data['upsInputCurrent'])) {
        $data['upsInputCurrent'] = $data['upsInputCurrent'][0];
        $oid .= '.0';
    }

    discover_sensor(
        $valid['sensor'],
        'current',
        $device,
        $oid,
        100 + $index,
        'rfc1628',
        $descr,
        $divisor,
        1,
        null,
        null,
        null,
        null,
        $data['upsInputCurrent'] / $divisor
    );
}

$bypass_current = snmpwalk_group($device, 'upsBypassCurrent', 'UPS-MIB');
foreach ($bypass_current as $index => $data) {
    $oid = ".1.3.6.1.2.1.33.1.5.3.1.3.$index";
    $divisor = get_device_divisor($device, $pre_cache['poweralert_serial'], 'current', $oid);
    $descr = 'Bypass';
    if (count($bypass_current) > 1) {
        $descr .= " Phase $index";
    }
    if (is_array($data['upsBypassCurrent'])) {
        $data['upsBypassCurrent'] = $data['upsBypassCurrent'][0];
        $oid .= '.0';
    }

    discover_sensor(
        $valid['sensor'],
        'current',
        $device,
        $oid,
        200 + $index,
        'rfc1628',
        $descr,
        $divisor,
        1,
        null,
        null,
        null,
        null,
        $data['upsBypassCurrent'] / $divisor
    );
}

unset($battery_current, $output_current, $input_current, $bypass_current);
