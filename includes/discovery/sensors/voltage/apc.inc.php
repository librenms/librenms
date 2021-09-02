<?php

// Battery Bus Voltage

$oids = snmp_get($device, '.1.3.6.1.4.1.318.1.1.1.2.2.8.0', '-OsqnU');
d_echo($oids . "\n");

if ($oids) {
    echo ' Battery Bus ';
    [$oid,$current] = explode(' ', $oids);
    $divisor = 1;
    $type = 'apc';
    $index = '2.2.8.0';
    $descr = 'Battery Bus';
    discover_sensor($valid['sensor'], 'voltage', $device, $oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $current);
}
unset($oids);

//Three Phase Detection & Support

$phasecount = $pre_cache['apcups_phase_count'];
    d_echo($phasecount);
    d_echo($pre_cache['apcups_phase_count']);
// Check for three phase UPS devices - else skip to normal discovery
if ($phasecount > 1) {
    $oids = snmpwalk_cache_oid($device, 'upsPhaseOutputVoltage', $oids, 'PowerNet-MIB');
    $in_oids = snmpwalk_cache_oid($device, 'upsPhaseInputVoltage', $in_oids, 'PowerNet-MIB');
    foreach ($oids as $index => $data) {
        $type = 'apcUPS';
        $descr = 'Phase ' . substr($index, -1) . ' Output';
        $voltage_oid = '.1.3.6.1.4.1.318.1.1.1.9.3.3.1.3.' . $index;
        $divisor = 1;
        $voltage = $data['upsPhaseOutputVoltage'] / $divisor;
        if ($voltage >= 0) {
            discover_sensor($valid['sensor'], 'voltage', $device, $voltage_oid, $index, $type, $descr, $divisor, 1, null, null, null, null, $voltage);
        }
    }
    unset($index);
    unset($data);
    foreach ($in_oids as $index => $data) {
        $type = 'apcUPS';
        $voltage_oid = '.1.3.6.1.4.1.318.1.1.1.9.2.3.1.3.' . $index;
        $divisor = 1;
        $voltage = $data['upsPhaseInputVoltage'] / $divisor;
        $in_index = '3.1.3.' . $index;
        if (substr($index, 0, 1) == 2 && $data['upsPhaseInputVoltage'] != -1) {
            $descr = 'Phase ' . substr($index, -1) . ' Bypass Input';
            discover_sensor($valid['sensor'], 'voltage', $device, $voltage_oid, $in_index, $type, $descr, $divisor, 0, null, null, null, null, $voltage);
        } elseif (substr($index, 0, 1) == 1) {
            $descr = 'Phase ' . substr($index, -1) . ' Input';
            discover_sensor($valid['sensor'], 'voltage', $device, $voltage_oid, $in_index, $type, $descr, $divisor, 0, null, null, null, null, $voltage);
        }
    }
} else {
    $oids = snmp_walk($device, '.1.3.6.1.4.1.318.1.1.8.5.3.3.1.3', '-OsqnU');
    d_echo($oids . "\n");
    if ($oids) {
        echo 'APC In ';
        $divisor = 1;
        $type = 'apc';
        foreach (explode("\n", $oids) as $data) {
            $data = trim($data);
            if ($data) {
                [$oid, $current] = explode(' ', $data, 2);
                $split_oid = explode('.', $oid);
                $index = $split_oid[(count($split_oid) - 3)];
                $oid = '.1.3.6.1.4.1.318.1.1.8.5.3.3.1.3.' . $index . '.1.1';
                $descr = 'Input Feed ' . chr(64 + $index);
                discover_sensor($valid['sensor'], 'voltage', $device, $oid, "3.3.1.3.$index", $type, $descr, $divisor, '1', null, null, null, null, $current);
            }
        }
    }
    $oids = snmp_walk($device, '.1.3.6.1.4.1.318.1.1.8.5.4.3.1.3', '-OsqnU');
    d_echo($oids . "\n");
    if ($oids) {
        echo ' APC Out ';
        $divisor = 1;
        $type = 'apc';
        foreach (explode("\n", $oids) as $data) {
            $data = trim($data);
            if ($data) {
                [$oid, $current] = explode(' ', $data, 2);
                $split_oid = explode('.', $oid);
                $index = $split_oid[(count($split_oid) - 3)];
                $oid = '.1.3.6.1.4.1.318.1.1.8.5.4.3.1.3.' . $index . '.1.1';
                $descr = 'Output Feed';
                if (count(explode("\n", $oids)) > 1) {
                    $descr .= " $index";
                }
                discover_sensor($valid['sensor'], 'voltage', $device, $oid, "4.3.1.3.$index", $type, $descr, $divisor, '1', null, null, null, null, $current);
            }
        }
    }
    $oids = snmp_get($device, '.1.3.6.1.4.1.318.1.1.1.3.2.1.0', '-OsqnU');
    d_echo($oids . "\n");
    if ($oids) {
        echo ' APC In ';
        [$oid,$current] = explode(' ', $oids);
        $divisor = 1;
        $type = 'apc';
        $index = '3.2.1.0';
        $descr = 'Input';
        discover_sensor($valid['sensor'], 'voltage', $device, $oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $current);
    }
    $oids = snmp_get($device, '.1.3.6.1.4.1.318.1.1.1.4.2.1.0', '-OsqnU');
    d_echo($oids . "\n");
    if ($oids) {
        echo ' APC Out ';
        [$oid,$current] = explode(' ', $oids);
        $divisor = 1;
        $type = 'apc';
        $index = '4.2.1.0';
        $descr = 'Output';
        discover_sensor($valid['sensor'], 'voltage', $device, $oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $current);
    }
    // rPDUIdentDeviceLinetoLineVoltage
    $oids = snmp_get($device, '.1.3.6.1.4.1.318.1.1.12.1.15.0', '-OsqnU');
    d_echo($oids . "\n");
    if ($oids) {
        echo ' Voltage In ';
        [$oid,$current] = explode(' ', $oids);
        if ($current >= 0) { // Newer units using rPDU2 can return the following rPDUIdentDeviceLinetoLineVoltage.0; Value (Integer): -1 hence this check.
            $divisor = 1;
            $type = 'apc';
            $index = '1';
            $descr = 'Input';
            discover_sensor($valid['sensor'], 'voltage', $device, $oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $current);
        }
    }
    // rPDU2PhaseStatusVoltage
    $oids = snmp_walk($device, '.1.3.6.1.4.1.318.1.1.26.6.3.1.6', '-OsqnU');
    d_echo($oids . "\n");
    if ($oids) {
        echo ' Voltage In ';
        [$oid,$current] = explode(' ', $oids);
        $divisor = 1;
        $type = 'apc';
        $index = '1';
        $descr = 'Input';
        discover_sensor($valid['sensor'], 'voltage', $device, $oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $current);
    }
}
