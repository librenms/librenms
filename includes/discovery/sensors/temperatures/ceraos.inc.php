<?php

if ($device['os'] == 'ceraos') {
    $mib  = 'MWRM-UNIT-MIB::genEquipUnitIduTemperature.0';
    $oid = ' .1.3.6.1.4.1.2281.10.1.1.9.0';
    $oids = snmp_get($device, $mib, '-OsqnU', 'MWRM-UNIT-MIB');
    d_echo($oids."\n");

    if (!empty($oids)) {
        echo ' Ceragon CeraOS Temperature ';

        $divisor = 1;
        $type    = 'ceraos';

        list(,$current) = explode(' ', $oids);
        $index          = $oid;
        $descr          = 'System Temp';
        discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $current);
    }
}
