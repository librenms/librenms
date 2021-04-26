<?php
/*
 * voltages for raspberry pi
 * requires snmp extend agent script from librenms-agent
 */
if (! empty($pre_cache['raspberry_pi_sensors'])) {
    $sensor_type = 'raspberry_volts';
    $oid = '.1.3.6.1.4.1.8072.1.3.2.4.1.2.9.114.97.115.112.98.101.114.114.121.';
    for ($volt = 2; $volt < 6; $volt++) {
        switch ($volt) {
            case '2':
                $descr = 'Core';
                break;
            case '3':
                $descr = 'SDRAMc';
                break;
            case '4':
                $descr = 'SDRAMi';
                break;
            case '5':
                $descr = 'SDRAMp';
                break;
        }
        $value = current($pre_cache['raspberry_pi_sensors']['raspberry.' . $volt]);
        if (is_numeric($value)) {
            discover_sensor($valid['sensor'], 'voltage', $device, $oid . $volt, $volt, $sensor_type, $descr, '1', '1', null, null, null, null, $value);
        } else {
            break;
        }
    }
}

$oids = snmp_walk($device, '.1.3.6.1.4.1.10876.2.1.1.1.1.3', '-OsqnU', 'SUPERMICRO-HEALTH-MIB', 'supermicro');
d_echo($oids . "\n");

$oids = trim($oids);
if ($oids) {
    echo 'Supermicro ';
}

$type = 'supermicro';
$divisor = '1000';
foreach (explode("\n", $oids) as $data) {
    $data = trim($data);
    if ($data) {
        [$oid,$kind] = explode(' ', $data);
        $split_oid = explode('.', $oid);
        $index = $split_oid[(count($split_oid) - 1)];
        if ($kind == 1) {
            $volt_oid = '.1.3.6.1.4.1.10876.2.1.1.1.1.4.' . $index;
            $descr_oid = '.1.3.6.1.4.1.10876.2.1.1.1.1.2.' . $index;
            $monitor_oid = '.1.3.6.1.4.1.10876.2.1.1.1.1.10.' . $index;
            $limit_oid = '.1.3.6.1.4.1.10876.2.1.1.1.1.5.' . $index;
            $lowlimit_oid = '.1.3.6.1.4.1.10876.2.1.1.1.1.6.' . $index;

            $descr = snmp_get($device, $descr_oid, '-Oqv', 'SUPERMICRO-HEALTH-MIB', 'supermicro');
            $current = (snmp_get($device, $volt_oid, '-Oqv', 'SUPERMICRO-HEALTH-MIB', 'supermicro') / $divisor);
            $limit = (snmp_get($device, $limit_oid, '-Oqv', 'SUPERMICRO-HEALTH-MIB', 'supermicro') / $divisor);
            $lowlimit = (snmp_get($device, $lowlimit_oid, '-Oqv', 'SUPERMICRO-HEALTH-MIB', 'supermicro') / $divisor);
            $monitor = snmp_get($device, $monitor_oid, '-Oqv', 'SUPERMICRO-HEALTH-MIB', 'supermicro');
            $descr = trim(str_ireplace('Voltage', '', $descr));

            if ($monitor == 'true') {
                discover_sensor($valid['sensor'], 'voltage', $device, $volt_oid, $index, $type, $descr, $divisor, '1', $lowlimit, null, null, $limit, $current);
            }
        }
    }//end if
}

if (preg_match('/(Linux).+(ntc)/', $device['sysDescr'])) {
    $sensor_type = 'chip_volts';
    $oid = '.1.3.6.1.4.1.8072.1.3.2.4.1.2.10.112.111.119.101.114.45.115.116.97.';
    $lowlimit = 3.8;
    $lowwarnlimit = 3.8;
    $warnlimit = 6.3;
    $limit = 6.3;
    $descr = 'AC IN voltage';
    $index = '116.2';
    $value = snmp_get($device, $oid . $index, '-Oqv');
    if (is_numeric($value)) {
        discover_sensor($valid['sensor'], 'voltage', $device, $oid . $index, $index, $sensor_type, $descr, '1', '1', $lowlimit, $lowwarnlimit, $warnlimit, $limit, $value);
    }
    $descr = 'VBUS voltage';
    $index = '116.4';
    $value = snmp_get($device, $oid . $index, '-Oqv');
    if (is_numeric($value)) {
        discover_sensor($valid['sensor'], 'voltage', $device, $oid . $index, $index, $sensor_type, $descr, '1', '1', $lowlimit, $lowwarnlimit, $warnlimit, $limit, $value);
    }
    $lowlimit = 2.75;
    $lowwarnlimit = 2.8;
    $warnlimit = 4.2;
    $limit = 4.2;
    $descr = 'Battery voltage';
    $index = '116.6';
    $value = snmp_get($device, $oid . $index, '-Oqv');
    if (is_numeric($value)) {
        discover_sensor($valid['sensor'], 'voltage', $device, $oid . $index, $index, $sensor_type, $descr, '1', '1', $lowlimit, $lowwarnlimit, $warnlimit, $limit, $value);
    }
}

$oids = '.1.3.6.1.4.1.8072.1.3.2.4.1.2.7.117.112.115.45.110.117.116.4';
$value = snmp_get($device, $oids, '-Osqnv');

if (! empty($value)) {
    $type = 'ups-nut';
    $index = 4;
    $limit = 60;
    $lowlimit = 0;
    $lowwarnlimit = 0;
    $descr = 'Battery Voltage';

    discover_sensor($valid['sensor'], 'voltage', $device, $oids, $index, $type, $descr, 1, 1, $lowlimit, $lowwarnlimit, null, $limit, $value);
}
unset($oids);

$oids = '.1.3.6.1.4.1.8072.1.3.2.4.1.2.7.117.112.115.45.110.117.116.5';
$value = snmp_get($device, $oids, '-Osqnv');

if (! empty($value)) {
    $type = 'ups-nut';
    $index = 5;
    $limit = 60;
    $lowlimit = 0;
    $lowwarnlimit = 0;
    $descr = 'Battery Nominal';

    discover_sensor($valid['sensor'], 'voltage', $device, $oids, $index, $type, $descr, 1, 1, $lowlimit, $lowwarnlimit, null, $limit, $value);
}
unset($oids);

$oids = '.1.3.6.1.4.1.8072.1.3.2.4.1.2.7.117.112.115.45.110.117.116.6';
$value = snmp_get($device, $oids, '-Osqnv');

if (! empty($value)) {
    $type = 'ups-nut';
    $index = 6;
    $limit = 0;
    $lowlimit = 0;
    $lowwarnlimit = 0;
    $descr = 'Line Nominal';

    discover_sensor($valid['sensor'], 'voltage', $device, $oids, $index, $type, $descr, 1, 1, $lowlimit, $lowwarnlimit, null, $limit, $value);
}
unset($oids);

$oids = '.1.3.6.1.4.1.8072.1.3.2.4.1.2.7.117.112.115.45.110.117.116.7';
$value = snmp_get($device, $oids, '-Osqnv');

if (! empty($value)) {
    $type = 'ups-nut';
    $index = 7;
    $limit = 280;
    $lowlimit = 200;
    $lowwarnlimit = 0;
    $descr = 'Input Voltage';

    discover_sensor($valid['sensor'], 'voltage', $device, $oids, $index, $type, $descr, 1, 1, $lowlimit, $lowwarnlimit, null, $limit, $value);
}
