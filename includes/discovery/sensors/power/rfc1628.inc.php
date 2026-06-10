<?php

use Illuminate\Support\Facades\Log;

echo 'RFC1628 ';

$output_power = snmpwalk_group($device, 'upsOutputPower', 'UPS-MIB');
foreach ($output_power as $index => $data) {
    $pwr_oid = ".1.3.6.1.2.1.33.1.4.4.1.4.$index";
    $descr = 'Output';
    if (count($output_power) > 1) {
        $descr .= " Phase $index";
    }
    $outputPowerValue = $data['upsOutputPower'] ?? null;
    if (is_array($outputPowerValue)) {
        $outputPowerValue = $outputPowerValue[0];
        $pwr_oid .= '.0';
    }

    if (! is_numeric($outputPowerValue)) {
        Log::debug("skipped $descr: $outputPowerValue is not numeric");

        continue;
    }

    discover_sensor(
        null,
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
        $outputPowerValue
    );
}

$input_power = snmpwalk_group($device, 'upsInputTruePower', 'UPS-MIB');
foreach ($input_power as $index => $data) {
    $pwr_oid = ".1.3.6.1.2.1.33.1.3.3.1.5.$index";
    $descr = 'Input';
    if (count($input_power) > 1) {
        $descr .= " Phase $index";
    }
    $trupInputPowerValue = $data['upsInputTruePower'] ?? null;
    if (is_array($trupInputPowerValue)) {
        $trupInputPowerValue = $trupInputPowerValue[0];
        $pwr_oid .= '.0';
    }

    if (! is_numeric($trupInputPowerValue)) {
        Log::debug("skipped $descr: $trupInputPowerValue is not numeric");

        continue;
    }

    discover_sensor(
        null,
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
        $trupInputPowerValue
    );
}

$bypass_power = snmpwalk_group($device, 'upsBypassPower', 'UPS-MIB');
foreach ($bypass_power as $index => $data) {
    $pwr_oid = ".1.3.6.1.2.1.33.1.5.3.1.4.$index";
    $descr = 'Bypass';
    if (count($bypass_power) > 1) {
        $descr .= " Phase $index";
    }
    $bypassPowerValue = $data['upsBypassPower'] ?? null;
    if (is_array($bypassPowerValue)) {
        $bypassPowerValue = $bypassPowerValue[0];
        $pwr_oid .= '.0';
    }

    if (! is_numeric($bypassPowerValue)) {
        Log::debug("skipped $descr: $bypassPowerValue is not numeric");

        continue;
    }

    discover_sensor(
        null,
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
        $bypassPowerValue
    );
}

unset($output_power, $input_power, $bypass_power);
