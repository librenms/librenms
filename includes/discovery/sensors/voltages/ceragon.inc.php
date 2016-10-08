<?php

if ($device['os'] == 'ceragon') {
    $mib  = 'MWRM-UNIT-MIB::genEquipUnitIduVoltageInput.0';
    $oid = '.1.3.6.1.4.1.2281.10.1.1.10.0';
    $oids = snmp_get($device, $mib, '-OsqnU', 'MWRM-UNIT-MIB');
    d_echo($oids."\n");

    if (!empty($oids)) {
        echo ' Ceragon Voltage ';

        $divisor = 1;
        $type    = 'ceragon';

        list(,$current) = explode(' ', $oids);
        $index          = $oid;
        $descr          = 'System voltage';
        discover_sensor($valid['sensor'], 'voltage', $device, $oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $current);
    }
}
