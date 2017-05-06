<?php

if ($device["os"] === "terra") {
    $descr = "Processor";

    $query = array(
       array("sti410C", ".1.3.6.1.4.1.30631.1.9.1.1.3.0"),
       array("sti440",  ".1.3.6.1.4.1.30631.1.18.1.326.3.0")
    );

    $sysDescr = strlen($device["sysDescr"]) == 0 ? snmp_get($device, ".1.3.6.1.2.1.1.1.0", "-Ovq") : $device["sysDescr"];

    foreach ($query as $row) {
        if (strpos($sysDescr, $row[0]) !== false) {
            $proc_usage = snmp_get($device, $row[1], "-Ovq");
            if (is_numeric($proc_usage)) {
                discover_processor($valid["processor"], $device, $row[1], "0", "cpu", $descr, "1", $proc_usage);
            }
        }
    }

    unset($query);
}
