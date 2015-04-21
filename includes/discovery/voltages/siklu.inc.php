<?php

if ($device['os'] == "siklu") {
    $oid = "rbSysVoltage.0";
    $oids = snmp_walk($device, "$oid", "-OsqnU", "RADIO-BRIDGE-MIB");
    if ($debug) { echo($oids."\n"); }
    if ($oids) echo("Siklu Voltage ");
    $divisor = 1;
    $type = "siklu";
    if ($oids) {
        list(,$current) = explode(' ',$oids);
        $index = $oid;
        $descr = "System voltage";
        discover_sensor($valid['sensor'], 'voltage', $device, $oid, $index, $type, $descr, $divisor, '1', NULL, NULL, NULL, NULL, $current);
    }
}
