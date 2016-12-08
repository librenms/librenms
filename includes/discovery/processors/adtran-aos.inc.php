<?php

/*
 * LibreNMS ADTRAN AOS Processor Discovery module
 *
 * Copyright (c) 2016 Chris A. Evans <thecityofguanyu@outlook.com>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */


if ($device['os'] == 'adtran-aos') {
    echo 'ADTRAN AOS:';
    $descr = 'Processor';
    $usage = snmp_get($device, '.1.3.6.1.4.1.664.5.53.1.4.1.0', '-Ovq');
    echo "This is the CP info AAAA $usage AAAA";

    if (is_numeric($usage)) {
        discover_processor($valid['processor'], $device, '.1.3.6.1.4.1.664.5.53.1.4.1.0', '0', 'adtran-aos', $descr, '1', $usage, null, null);
    }
}
