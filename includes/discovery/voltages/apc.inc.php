<?php

// APC Voltages
if ($device['os'] == 'apc') {
    $oids = snmp_walk($device, '1.3.6.1.4.1.318.1.1.8.5.3.3.1.3', '-OsqnU', '');
    d_echo($oids."\n");

    if ($oids) {
        echo 'APC In ';
    }

    $divisor = 1;
    $type    = 'apc';
    foreach (explode("\n", $oids) as $data) {
        $data = trim($data);
        if ($data) {
            list($oid,$current) = explode(' ', $data, 2);
            $split_oid          = explode('.', $oid);
            $index              = $split_oid[(count($split_oid) - 3)];
            $oid                = '1.3.6.1.4.1.318.1.1.8.5.3.3.1.3.'.$index.'.1.1';
            $descr              = 'Input Feed '.chr(64 + $index);

            discover_sensor($valid['sensor'], 'voltage', $device, $oid, "3.3.1.3.$index", $type, $descr, $divisor, '1', null, null, null, null, $current);
        }
    }

    $oids = snmp_walk($device, '1.3.6.1.4.1.318.1.1.8.5.4.3.1.3', '-OsqnU', '');
    d_echo($oids."\n");

    if ($oids) {
        echo ' APC Out ';
    }

    $divisor = 1;
    $type    = 'apc';
    foreach (explode("\n", $oids) as $data) {
        $data = trim($data);
        if ($data) {
            list($oid,$current) = explode(' ', $data, 2);
            $split_oid          = explode('.', $oid);
            $index              = $split_oid[(count($split_oid) - 3)];
            $oid                = '1.3.6.1.4.1.318.1.1.8.5.4.3.1.3.'.$index.'.1.1';
            $descr              = 'Output Feed';
            if (count(explode("\n", $oids)) > 1) {
                $descr .= " $index";
            }

            discover_sensor($valid['sensor'], 'voltage', $device, $oid, "4.3.1.3.$index", $type, $descr, $divisor, '1', null, null, null, null, $current);
        }
    }

    $oids = snmp_get($device, '1.3.6.1.4.1.318.1.1.1.3.2.1.0', '-OsqnU', '');
    d_echo($oids."\n");

    if ($oids) {
        echo ' APC In ';
        list($oid,$current) = explode(' ', $oids);
        $divisor            = 1;
        $type               = 'apc';
        $index              = '3.2.1.0';
        $descr              = 'Input';

        discover_sensor($valid['sensor'], 'voltage', $device, $oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $current);
    }

    $oids = snmp_get($device, '1.3.6.1.4.1.318.1.1.1.4.2.1.0', '-OsqnU', '');
    d_echo($oids."\n");

    if ($oids) {
        echo ' APC Out ';
        list($oid,$current) = explode(' ', $oids);
        $divisor            = 1;
        $type               = 'apc';
        $index              = '4.2.1.0';
        $descr              = 'Output';

        discover_sensor($valid['sensor'], 'voltage', $device, $oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $current);
    }

    // PDU
    // $oids = snmp_get($device, "1.3.6.1.4.1.318.1.1.12.1.15.0", "-OsqnU", "");
    $oids = snmp_walk($device, 'rPDUIdentDeviceLinetoLineVoltage.0', '-OsqnU', 'PowerNet-MIB');
    d_echo($oids."\n");

    if ($oids) {
        echo ' Voltage In ';
        list($oid,$current) = explode(' ', $oids);
        $divisor            = 1;
        $type               = 'apc';
        $index              = '1';
        $descr              = 'Input';

        discover_sensor($valid['sensor'], 'voltage', $device, $oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $current);
    }

    $oids = snmp_walk($device, '1.3.6.1.4.1.318.1.1.26.6.3.1.6', '-OsqnU', 'PowerNet-MIB');
    d_echo($oids."\n");

    if ($oids) {
        echo ' Voltage In ';
        list($oid,$current) = explode(' ', $oids);
        $divisor            = 1;
        $type               = 'apc';
        $index              = '1';
        $descr              = 'Input';

        discover_sensor($valid['sensor'], 'voltage', $device, $oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $current);
    }
}//end if
