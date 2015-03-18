<?php
/*
* LibreNMS Cisco Small Business CPU information module
*
* Copyright (c) 2015 Mike Rostermund <mike@kollegienet.dk>
* This program is free software: you can redistribute it and/or modify it
* under the terms of the GNU General Public License as published by the
* Free Software Foundation, either version 3 of the License, or (at your
* option) any later version.  Please see LICENSE.txt at the top level of
* the source code distribution for details.
*/

if ($device['os'] == "ciscosb") {
    echo("Cisco SB : ");

    $descr = "CPU";
    $usage = snmp_get($device, "rlCpuUtilDuringLastMinute", "-Ovqn", "CISCOSB-rndMng");

    if (is_numeric($usage)) {
        discover_processor($valid['processor'], $device, "CISCOSB-rndMng::rlCpuUtilDuringLastMinute", "0", "ciscosb", $descr, "1", $usage, NULL, NULL);
    }
}

unset ($processors_array);

?>
