<?php

/*
 * LibreNMS Accedian MetroNID Processor Discovery module
 *
 * Copyright (c) 2016 Chris A. Evans <thecityofguanyu@outlook.com>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */



if ($device['os'] == 'accedian') {
 if ($device['version'] == 'AEN_5.3.1_22558') { 
 } // don't poll 5.3.1_22558 devices due to bug that crashes snmpd
 else {
    echo 'Accedian MetroNID:';
    $descr = 'Processor';
    $usage = snmp_get($device, '.1.3.6.1.4.1.22420.1.1.20.0', '-Ovq');
    $usage = trim($usage, "percent");
    echo "usage";

    if (is_numeric($usage)) {
        discover_processor($valid['processor'], $device, '.1.3.6.1.4.1.22420.1.1.20.0', '0', 'accedian', $descr, '1', $usage, null, null);
    }
  }
}
