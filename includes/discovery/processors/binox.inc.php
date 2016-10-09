<?php

/*
 * LibreNMS Telco Systems Processor Discovery module
 *
 * Copyright (c) 2016 Chris A. Evans <thecityofguanyu@outlook.com>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */


if ($device['os'] == 'binox') {
    if (strpos($device['sysObjectID'], 'enterprises.738.10.5.100') !== false) {
        if ($device['version'] == '2.4.R3.1.1') {
            echo 'Telco Systems:';
            $descr = 'Processor';
            $usage = snmp_get($device, '.1.3.6.1.4.1.738.10.111.1.1.3.1.1.0', '-Ovq');
            echo "This is the CP info AAAA $usage AAAA";

            if (is_numeric($usage)) {
                discover_processor($valid['processor'], $device, '.1.3.6.1.4.1.738.10.111.1.1.3.1.1.0', '0', 'binox', $descr, '1', $usage, null, null);
            }
        } else {
            echo 'Telco Systems:';
            $descr = 'Processor';
            $usage = snmp_get($device, '.1.3.6.1.4.1.738.10.111.3.1.1.0', '-Ovq');
            $usage = str_replace('%', '', $usage);
             $usage = str_replace('"', '', $usage);

            if (is_numeric($usage)) {
                discover_processor($valid['processor'], $device, '.1.3.6.1.4.1.738.10.111.3.1.1.0', '0', 'binox', $descr, '1', $usage, null, null);
            }
        }
    }
}
