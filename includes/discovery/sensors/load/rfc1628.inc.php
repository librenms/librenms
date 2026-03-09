<?php

use Illuminate\Support\Facades\Log;
use LibreNMS\Util\Number;

echo 'RFC1628 ';

$load_data = snmpwalk_group($device, 'upsOutputPercentLoad', 'UPS-MIB');

foreach ($load_data as $index => $data) {
    $descr = 'Percentage load';
    $load_oid = ".1.3.6.1.2.1.33.1.4.4.1.5.$index";

    $value = $data['upsOutputPercentLoad'] ?? null;
    if (is_array($value)) {
        $load_oid .= '.0';
        $value = $value[0];
    }

    if (! is_numeric($value)) {
        Log::debug("skipped $descr: $value is not numeric");

        continue;
    }

    $divisor = get_device_divisor($device, $pre_cache['poweralert_serial'] ?? 0, 'load', $load_oid);

    if (count($load_data) > 1) {
        $descr .= " $index";
    }

    discover_sensor(
        null,
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
        Number::cast($value) / $divisor
    );
}
