<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2017 Simone Fini <tomfordfirst@gmail.com> 
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */
if ($device['os'] == 'alteonos') {
    $oid = '.1.3.6.1.4.1.1872.2.5.1.2.2.1.0';
    $usage = snmp_walk($device, $oid, '-Ovq');
    discover_processor($valid['processor'], $device, $oid, 0, 'alteonos', 'Processor', '1', $usage);
}
