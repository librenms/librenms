<?php
/*
 * cpu temp for raspberry pi
 * requires snmp extend agent script from librenms-agent
 */

$raspberry = snmp_get($device, 'HOST-RESOURCES-MIB::hrSystemInitialLoadParameters.0', '-Osqnv');

if (preg_match("/(bcm).+(boardrev)/", $raspberry)) {
    $sensor_type = "raspberry_temp";
    $sensor_oid = ".1.3.6.1.4.1.8072.1.3.2.4.1.2.9.114.97.115.112.98.101.114.114.121.1";
    $descr = "CPU Temp";
    $value = snmp_get($device, $sensor_oid, '-Oqve');
    if (is_numeric($value)) {
        discover_sensor($valid['sensor'], 'temperature', $device, $sensor_oid, 1, $sensor_type, $descr, 1, 1, null, null, null, null, $value);
    }
}

echo 'HP_ILO ';
$oids = snmp_walk($device, '.1.3.6.1.4.1.232.6.2.6.8.1.2.1', '-Osqn', '');
$oids = trim($oids);
foreach (explode("\n", $oids) as $data) {
    $data = trim($data);
    if ($data != '') {
        list($oid)      = explode(' ', $data);
        $split_oid      = explode('.', $oid);
        $temperature_id = $split_oid[(count($split_oid) - 2)].'.'.$split_oid[(count($split_oid) - 1)];

        $descr_oid = ".1.3.6.1.4.1.232.6.2.6.8.1.3.$temperature_id";
        $descr     = snmp_get($device, $descr_oid, '-Oqnv', 'CPQHLTH-MIB');

        $temperature_oid = ".1.3.6.1.4.1.232.6.2.6.8.1.4.$temperature_id";
        $temperature     = snmp_get($device, $temperature_oid, '-Oqv', '');

        $threshold_oid = ".1.3.6.1.4.1.232.6.2.6.8.1.5.$temperature_id";
        $threshold     = snmp_get($device, $threshold_oid, '-Oqv', '');

        if (!empty($temperature)) {
            discover_sensor($valid['sensor'], 'temperature', $device, $temperature_oid, $oid, 'hpilo', $descr, '1', '1', null, null, null, $threshold, $temperature);
        }
    }
}

// Supermicro sensors
$oids = snmp_walk($device, '.1.3.6.1.4.1.10876.2.1.1.1.1.3', '-Osqn', 'SUPERMICRO-HEALTH-MIB');
$oids = trim($oids);
if ($oids) {
    echo 'Supermicro ';
    foreach (explode("\n", $oids) as $data) {
        $data = trim($data);
        if ($data) {
            list($oid, $type) = explode(' ', $data);
            $oid_ex = explode('.', $oid);
            $index = $oid_ex[(count($oid_ex) - 1)];
            if ($type == 2) {
                $temperature_oid = ".1.3.6.1.4.1.10876.2.1.1.1.1.4.$index";
                $descr_oid = ".1.3.6.1.4.1.10876.2.1.1.1.1.2.$index";
                $limit_oid = ".1.3.6.1.4.1.10876.2.1.1.1.1.5.$index";
                $divisor_oid = ".1.3.6.1.4.1.10876.2.1.1.1.1.9.$index";
                $monitor_oid = ".1.3.6.1.4.1.10876.2.1.1.1.1.10.$index";
                $descr = snmp_get($device, $descr_oid, '-Oqv', 'SUPERMICRO-HEALTH-MIB');
                $temperature = snmp_get($device, $temperature_oid, '-Oqv', 'SUPERMICRO-HEALTH-MIB');
                $limit = snmp_get($device, $limit_oid, '-Oqv', 'SUPERMICRO-HEALTH-MIB');
                $divisor = snmp_get($device, $divisor_oid, '-Oqv', 'SUPERMICRO-HEALTH-MIB') || 1;
                $monitor = snmp_get($device, $monitor_oid, '-Oqv', 'SUPERMICRO-HEALTH-MIB');
                if ($monitor == 'true') {
                    $descr = trim(str_ireplace('temperature', '', $descr));
                    discover_sensor($valid['sensor'], 'temperature', $device, $temperature_oid, trim($index, '.'), 'supermicro', $descr, (int)$divisor, '1', null, null, null, $limit, $temperature);
                }
            }
        }
    }
}
