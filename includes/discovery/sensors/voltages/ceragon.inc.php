<?php

if ($device['os'] == 'ceragon') {
    $mib  = 'genEquipUnitIduVoltageInput';
    $oids = snmp_get_next($device, $mib, '-OsqnU', 'MWRM-RADIO-MIB');
    $oid = strtok($oids, " ");
    d_echo($oids."\n");

    if (!empty($oids)) {
        echo 'Ceragon Voltage ';

        $divisor = 1;
        $type    = 'ceragon';

        list(,$current) = explode(' ', $oids);
        $index          = $oid;
        $descr          = 'System Input Voltage';
        discover_sensor($valid['sensor'], 'voltage', $device, $oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $current);
    }
}
?>
