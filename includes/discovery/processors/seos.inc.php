<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2017Dave Bell <me@geordish.org>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

if ($device['os'] == 'seos') {
    echo 'Redback SEOS : ';

    $oid = '.1.3.6.1.4.1.2352.2.6.1.1.0';
    $descr = 'Processor';
    $usage = snmp_get($device, $oid, '-Ovqn');

    if (is_numeric($usage)) {
        discover_processor($valid['processor'], $device, $oid, '0', 'seos', $descr, '1', $usage);
    }
}
