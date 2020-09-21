<?php

if (strstr($device['sysDescr'], 'IBM Flex System Fabric')) {
    $check_oids = snmp_get($device, '.1.3.6.1.4.1.20301.2.5.1.3.1.22.0', '-OsqnU', '');
    if ($check_oids !== false) {
        $index = 0;
        echo 'IBM Flex System Fabric ';
        $temps = [];
        $temps['.1.3.6.1.4.1.20301.2.5.1.3.1.22.0'] = 'Temperature Sensor 1';
        $temps['.1.3.6.1.4.1.20301.2.5.1.3.1.23.0'] = 'Temperature Sensor 2';
        $temps['.1.3.6.1.4.1.20301.2.5.1.3.1.26.0'] = 'Temperature Sensor 3';
        $temps['.1.3.6.1.4.1.20301.2.5.1.3.1.27.0'] = 'Temperature Sensor 4';
        if (strstr($device['hardware'], 'EN4093R 10Gb Scalable Switch')) {
            $temps['.1.3.6.1.4.1.20301.2.5.1.3.1.36.0'] = 'Temperature Sensor 5';
        }

        foreach ($temps as $obj => $descr) {
            $oids = snmp_get($device, $obj, '-OsqnU', '');
            [,$current] = explode(' ', $oids);
            $index = $obj;
            $divisor = '1';
            $multiplier = '1';
            $type = 'ibmnos';
            discover_sensor($valid['sensor'], 'temperature', $device, $obj, $index, $type, $descr, $divisor, $multiplier, null, null, null, null, $current);
        }
    }
}
