<?php

use Illuminate\Support\Facades\Log;
use LibreNMS\Util\Number;

echo 'RFC1628 ';

$battery_current = snmp_get($device, 'upsBatteryCurrent.0', '-OqvU', 'UPS-MIB');

if (is_numeric($battery_current)) {
    $oid = '.1.3.6.1.2.1.33.1.2.6.0';
    $divisor = get_device_divisor($device, $pre_cache['poweralert_serial'] ?? '', 'current', $oid);

    discover_sensor(
        null,
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
    $divisor = get_device_divisor($device, $pre_cache['poweralert_serial'] ?? '', 'current', $oid);
    $descr = 'Output';
    if (count($output_current) > 1) {
        $descr .= " Phase $index";
    }
    $outputCurrentValue = $data['upsOutputCurrent'] ?? null;
    if (is_array($outputCurrentValue)) {
        $outputCurrentValue = $outputCurrentValue[0];
        $oid .= '.0';
    }

    if (! is_numeric($outputCurrentValue)) {
        Log::debug("skipped $descr: $outputCurrentValue is not numeric");

        continue;
    }

    discover_sensor(
        null,
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
        Number::cast($outputCurrentValue) / $divisor
    );
}

$input_current = snmpwalk_group($device, 'upsInputCurrent', 'UPS-MIB');
foreach ($input_current as $index => $data) {
    $oid = ".1.3.6.1.2.1.33.1.3.3.1.4.$index";
    $divisor = get_device_divisor($device, $pre_cache['poweralert_serial'] ?? '', 'current', $oid);
    $descr = 'Input';
    if (count($input_current) > 1) {
        $descr .= " Phase $index";
    }
    $inputCurrentValue = $data['upsInputCurrent'] ?? null;
    if (is_array($inputCurrentValue)) {
        $inputCurrentValue = $inputCurrentValue[0];
        $oid .= '.0';
    }

    if (! is_numeric($inputCurrentValue)) {
        Log::debug("skipped $descr: $inputCurrentValue is not numeric");

        continue;
    }

    discover_sensor(
        null,
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
        Number::cast($inputCurrentValue) / $divisor
    );
}

$bypass_current = snmpwalk_group($device, 'upsBypassCurrent', 'UPS-MIB');
foreach ($bypass_current as $index => $data) {
    $oid = ".1.3.6.1.2.1.33.1.5.3.1.3.$index";
    $divisor = get_device_divisor($device, $pre_cache['poweralert_serial'] ?? '', 'current', $oid);
    $descr = 'Bypass';
    if (count($bypass_current) > 1) {
        $descr .= " Phase $index";
    }
    $bypassCurrentValue = $data['upsBypassCurrent'] ?? null;
    if (is_array($bypassCurrentValue)) {
        $bypassCurrentValue = $bypassCurrentValue[0];
        $oid .= '.0';
    }

    if (! is_numeric($bypassCurrentValue)) {
        Log::debug("skipped $descr: $bypassCurrentValue is not numeric");

        continue;
    }

    discover_sensor(
        null,
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
        Number::cast($bypassCurrentValue) / $divisor
    );
}

unset($battery_current, $output_current, $input_current, $bypass_current);
