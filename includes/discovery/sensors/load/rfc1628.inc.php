<?php

echo 'RFC1628 ';

$load_data = snmpwalk_group($device, 'upsOutputPercentLoad', 'UPS-MIB');

foreach ($load_data as $index => $data) {
    $load_oid = ".1.3.6.1.2.1.33.1.4.4.1.5.$index";

    if (is_array($data['upsOutputPercentLoad'])) {
        $load_oid .= '.0';
        $value = $data['upsOutputPercentLoad'][0];
    } else {
        $value = $data['upsOutputPercentLoad'];
    }

    $divisor = get_device_divisor($device, $pre_cache['poweralert_serial'], 'load', $load_oid);
    $descr = 'Percentage load';
    if (count($load_data) > 1) {
        $descr .= " $index";
    }

    discover_sensor(
        $valid['sensor'],
        'load',
        $device,
        $load_oid,
        500 + $index,
        'rfc1628',
        $descr,
        $divisor,
        1,
        null,
        null,
        null,
        null,
        $value / $divisor
    );
}
