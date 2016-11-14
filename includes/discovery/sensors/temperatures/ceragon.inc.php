<?php

if ($device['os'] == 'ceragon') {
    $mib  = 'genEquipUnitIduTemperature';
    $oids = snmp_get_next($device, $mib, '-OsqnU', 'MWRM-RADIO-MIB');
    $oid = strtok($oids, " ");
    d_echo($oids."\n");

    if (!empty($oids)) {
        echo 'Ceragon Temperature '."\n";
        $divisor = 1;
        $type    = 'ceragon';

        list(,$current) = explode(' ', $oids);
        $index          = $oid;
        $descr          = 'System Temp';
        discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $current);
    } else {
        echo 'Cant get Ceragon Temp';
    }
}
