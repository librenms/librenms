<?php

use LibreNMS\Enum\SensorType;

// Newer ATS firmware (ats5g) exposes per-source frequency on dedicated
// OIDs, so the legacy input OIDs report the selected source on both
// feeds there. Use the ats5g OIDs when the device has them, mirroring
// the input voltage fix, keeping the legacy sensor indexes so RRD
// history is preserved.
$ats5g_freq_oids = [
    1 => '.1.3.6.1.4.1.318.1.1.33.3.1.24.1',
    2 => '.1.3.6.1.4.1.318.1.1.33.3.1.25.1',
];
$ats5g_freq_values = [];
foreach ($ats5g_freq_oids as $index => $oid) {
    $value = SnmpQuery::get($oid)->value();
    if (is_numeric($value) && $value >= 0) {
        $ats5g_freq_values[$index] = $value;
    }
}

if (count($ats5g_freq_values) === 2) {
    echo 'APC In ';
    $divisor = 1;
    $type = 'apc';
    foreach ($ats5g_freq_values as $index => $current) {
        $descr = 'Input Feed ' . chr(64 + $index);
        discover_sensor(null, SensorType::Frequency, $device, $ats5g_freq_oids[$index], "3.2.1.4.$index", $type, $descr, $divisor, '1', null, null, null, null, $current);
    }
} else {
    $oids = snmp_walk($device, '.1.3.6.1.4.1.318.1.1.8.5.3.2.1.4', '-OsqnU', '');
    d_echo($oids . "\n");

    if ($oids) {
        echo 'APC In ';
    }

    $divisor = 1;
    $type = 'apc';
    foreach (explode("\n", (string) $oids) as $data) {
        $data = trim($data);
        if ($data) {
            [$oid,$current] = explode(' ', $data, 2);
            $split_oid = explode('.', $oid);
            $index = $split_oid[count($split_oid) - 1];
            $oid = '.1.3.6.1.4.1.318.1.1.8.5.3.2.1.4.' . $index;
            $descr = 'Input Feed ' . chr(64 + $index);
            discover_sensor(null, SensorType::Frequency, $device, $oid, "3.2.1.4.$index", $type, $descr, $divisor, '1', null, null, null, null, $current);
        }
    }
}

$oids = snmp_walk($device, '.1.3.6.1.4.1.318.1.1.8.5.4.2.1.4', '-OsqnU', '');
d_echo($oids . "\n");

if ($oids) {
    echo ' APC Out ';
}

$divisor = 1;
$type = 'apc';
foreach (explode("\n", (string) $oids) as $data) {
    $data = trim($data);
    if ($data) {
        [$oid,$current] = explode(' ', $data, 2);
        $split_oid = explode('.', $oid);
        $index = $split_oid[count($split_oid) - 3];
        $oid = '.1.3.6.1.4.1.318.1.1.8.5.4.2.1.4.' . $index;
        $descr = 'Output Feed';
        if (count(explode("\n", (string) $oids)) > 1) {
            $descr .= " $index";
        }

        discover_sensor(null, SensorType::Frequency, $device, $oid, "4.2.1.4.$index", $type, $descr, $divisor, '1', null, null, null, null, $current);
    }
}

// upsHighPrecInputFrequency
$oids = snmp_get($device, '.1.3.6.1.4.1.318.1.1.1.3.3.4.0', '-OsqnU', '');
d_echo($oids . "\n");
$divisor = 10;
$index = '3.3.4.0';

if (! $oids) {
    // upsAdvInputFrequency, used in case high precision is not available
    $oids = snmp_get($device, '.1.3.6.1.4.1.318.1.1.1.3.2.4.0', '-OsqnU', '');
    d_echo($oids . "\n");
    $divisor = 1;
    $index = '3.2.4.0';
}

if ($oids) {
    echo ' APC In ';
    [$oid,$current] = explode(' ', $oids);
    $type = 'apc';
    $descr = 'Input';
    discover_sensor(null, SensorType::Frequency, $device, $oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $current / $divisor);
}

// upsHighPrecOutputFrequency
$oids = snmp_get($device, '.1.3.6.1.4.1.318.1.1.1.4.3.2.0', '-OsqnU', '');
d_echo($oids . "\n");
$divisor = 10;
$index = '4.3.2.0';

if (! $oids) {
    // upsAdvOutputFrequency, used in case high precision is not available
    $oids = snmp_get($device, '.1.3.6.1.4.1.318.1.1.1.4.2.2.0', '-OsqnU', '');
    d_echo($oids . "\n");
    $divisor = 1;
    $index = '4.2.2.0';
}

if ($oids) {
    echo ' APC Out ';
    [$oid,$current] = explode(' ', $oids);
    $type = 'apc';
    $descr = 'Output';
    discover_sensor(null, SensorType::Frequency, $device, $oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $current / $divisor);
}
