<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2017 Søren Friis Rosiak <sorenrosiak@gmail.com>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

if ($device['os'] === 'hpmsm') {
    echo 'HPE MSM : ';
    $descr = 'Processor';
    $usage = snmp_get($device, 'coUsInfoCpuUseNow.0', '-OQUvs', 'COLUBRIS-USAGE-INFORMATION-MIB', 'hpmsm');
    if (is_numeric($usage)) {
        discover_processor($valid['processor'], $device, '.1.3.6.1.4.1.8744.5.21.1.1.5.0', '0', 'hpmsm', $descr, '1', $usage);
    }
}
