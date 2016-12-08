<?php

/*
 * LibreNMS Calix E7-2 Processor Discovery module
 *
 * Copyright (c) 2016 Chris A. Evans <thecityofguanyu@outlook.com>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */


if ($device['os'] == 'calix') {
    echo 'Calix: ';

    $descr = 'CPU';
    $usage = snmp_get($device, '.1.3.6.1.2.1.25.3.3.1.2.768', '-Ovqn');
    echo "This is the CPU usage percentage: $usage";

    if (is_numeric($usage)) {
        discover_processor($valid['processor'], $device, '.1.3.6.1.2.1.25.3.3.1.2.768', '0', 'calix', $descr, '1', $usage, null, null);
    }
}
