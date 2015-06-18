<?php

if ($device['os'] == "equallogic") {
    $oids = snmp_walk($device, "eqlControllerProcessorTemp", "-OsqnU", "EQLCONTROLLER-MIB", $config['install_dir']."/mibs/equallogic");
    d_echo($oids."\n");
    if ($oids !== false) echo("EQLCONTROLLER-MIB ");

    foreach (explode("\n", $oids) as $data) {
        $data = trim($data);
        if ($data) {
            list($oid,$temperature) = explode(" ", $data,2);
            $split_oid = explode('.',$oid);
            $index = $split_oid[count($split_oid)-1];
            $descr = "Contoller Proc Temp " . ($index-1);
            $index = 100+$index;

            discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, 'snmp', $descr, 1, '1', NULL, NULL, NULL, NULL, $temperature);
        }
    }

    $oids = snmp_walk($device, "eqlControllerChipsetTemp", "-OsqnU", "EQLCONTROLLER-MIB", $config['install_dir']."/mibs/equallogic");
    d_echo($oids."\n");
    if ($oids !== false) echo("EQLCONTROLLER-MIB ");

    foreach (explode("\n", $oids) as $data) {
        $data = trim($data);
        if ($data) {
            list($oid,$temperature) = explode(" ", $data,2);
            $split_oid = explode('.',$oid);
            $index = $split_oid[count($split_oid)-1];
            $descr = "Contoller Chipset Temp " . ($index-1);
            $index = 200+$index;

            discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, 'snmp', $descr, 1, '1', NULL, NULL, NULL, NULL, $temperature);
        }
    }

}
