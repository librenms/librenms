<?php

$oids = snmp_walk($device, 'flowMeter', '-OsqnU', 'DOMOTICS-MIB');
d_echo($oids."\n");

if ($oids !== false) {
        $extra_mibs = array(
            "domotics" => "DOMOTICS-MIB",
            "webweaving" => "WEBWEAVING-MIB",
        );
        register_mibs($device, $extra_mibs, "includes/discovery/os/3739.inc.php");
}

