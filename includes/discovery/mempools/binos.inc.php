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



if ($device['os'] == "binos") {
    echo("telco systems: ");

    $used       = snmp_get($device, ".1.3.6.1.4.1.738.1.111.2.1.1.5.0", "-Ovq"); // PRVT-SYS-INFO-MIB::numBytesAlloc.0 = INTEGER: 48993320
    $free       = snmp_get($device, ".1.3.6.1.4.1.738.1.111.2.1.1.1.0", "-Ovq"); // PRVT-SYS-INFO-MIB::numBytesFree.0 = INTEGER: 183136616
    $total      = ($free + $used);
    $percent    = (($used / $total) * 100);

    if (is_numeric($used)) {
        discover_mempool($valid_mempool, $device, 0, "binos", "Memory", "1", null, null);
    }
}
