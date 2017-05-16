<?php

if ($device['os'] === 'timos') {
    $oids = snmp_walk($device, 'tmnxHwID', '-Osqn', 'TIMETRA-CHASSIS-MIB', 'aos');
    $oids = trim($oids);
    foreach (explode("\n", $oids) as $data) {
        $data = trim($data);
        if ($data) {
            list($oid)                = explode(' ', $data);
            $oid                      = implode('.', array_slice(explode('.', $oid), -2, 2));
            $temperature_oid          = ".1.3.6.1.4.1.6527.3.1.2.2.1.8.1.18.$oid";
            $descr_oid                = ".1.3.6.1.4.1.6527.3.1.2.2.1.8.1.8.$oid";
            $temp_present_oid         = ".1.3.6.1.4.1.6527.3.1.2.2.1.8.1.17.$oid";
            $temp_high_threshold_oid  = ".1.3.6.1.4.1.6527.3.1.2.2.1.8.1.19.$oid";
            $descr                    = snmp_get($device, $descr_oid, '-Oqv', 'TIMETRA-CHASSIS-MIB', 'nokia');
            $temp_present             = snmp_get($device, $temp_present_oid, '-Oqv', 'TIMETRA-CHASSIS-MIB', 'nokia');
            $temperature              = snmp_get($device, $temperature_oid, '-OUqv', 'TIMETRA-CHASSIS-MIB', 'nokia');
            $temp_thresh              = snmp_get($device, $temp_high_threshold_oid, '-OUqv', 'TIMETRA-CHASSIS-MIB', 'nokia');
            if ($temp_present == true && $descr != '' && $temperature != -1) {
                $descr = str_replace('"', '', $descr);
                $descr = str_replace('temperature', '', $descr);
                $descr = str_replace('temperature', '', $descr);
                $descr = str_replace('sensor', '', $descr);
                $descr = ucfirst(trim($descr));

                discover_sensor($valid['sensor'], 'temperature', $device, $temperature_oid, $oid, 'nokia', $descr, '1', '1', null, null, null, $temp_thresh, $temperature);
            }
        }
    }
}
