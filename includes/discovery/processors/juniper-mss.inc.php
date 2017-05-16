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

if ($device['os'] === 'juniper-mss') {
    d_echo('Juniper MSS : ');
    $descr = 'Processor';
    $proc_usage = snmp_get($device, 'trpzSysCpuInstantLoad.0', '-Ovq', 'TRAPEZE-NETWORKS-SYSTEM-MIB');
    if (is_numeric($proc_usage)) {
        discover_processor($valid['processor'], $device, '.1.3.6.1.4.1.14525.4.8.1.1.11.1.0', '0', 'juniper-mss', $descr, '1', $proc_usage);
    }
}
