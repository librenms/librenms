<?php

if ($device["os"] === "terra") {
    $query = array(
        array("sti410C", ".1.3.6.1.4.1.30631.1.9.1.1.4.0"),
        array("sti440",  ".1.3.6.1.4.1.30631.1.18.1.326.4.0")
    );

    $sysDescr = strlen($device["sysDescr"]) == 0 ? snmp_get($device, ".1.3.6.1.2.1.1.1.0", "-Ovq") : $device["sysDescr"];

    foreach ($query as $row) {
        if (strpos($sysDescr, $row[0]) !== false) {
            $temperature = snmp_get($device, $row[1], "-Oqv");
            if (is_numeric($temperature)) {
                discover_sensor($valid["sensor"], "temperature", $device, $row[1], "0", $row[0], "Internal Temperature", 1, 1, null, null, null, null, $temperature);
            }
        }
    }

    unset($query);
}
