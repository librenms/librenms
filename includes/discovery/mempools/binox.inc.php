<?php

/*
 * LibreNMS Telco Systems RAM discovery module
 *
 * Copyright (c) 2016 Chris A. Evans <thecityofguanyu@outlook.com>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */



if ($device['os'] == "binox") {
    echo("telco systems: ");

    $used       = snmp_get($device, ".1.3.6.1.4.1.738.10.111.3.1.3.0", "-Ovq");
    $used       = str_replace('%', '', $used);
    $used       = str_replace('"', '', $used);
    $total      = "100";
    $free       = ($total - $used);
    $percent    = $used;

    if (is_numeric($used)) {
        discover_mempool($valid_mempool, $device, 0, "binox", "Memory", "1", null, null);
    }
}
