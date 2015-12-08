<?php

if ($device['os'] == "nos") {
    echo("nos : ");

    $descr = "CPU";
    $usage = snmp_get($device, "1.3.6.1.4.1.1588.2.1.1.1.26.1.0", "-Ovq");

    if (is_numeric($usage)) {
        discover_processor($valid['processor'], $device, "1.3.6.1.4.1.1588.2.1.1.1.26.1", "0", "nos", $descr, "1", $usage, NULL, NULL);
    }
}

unset ($processors_array);
