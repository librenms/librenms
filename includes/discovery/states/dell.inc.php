<?php

/*
 * LibreNMS
 *
 * Copyright (c) 2014 Neil Lathwood <https://github.com/laf/ http://www.lathwood.co.uk/fa>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

if ($device['os'] == 'linux') {
    $oids     = snmp_walk($device, 'virtualDiskNumber', '-Oesqn', 'StorageManagement-MIB');
    $main_oid = '.1.3.6.1.4.1.674.10893.1.20.140.1.1.';
    d_echo($oids."\n");

    $oids = trim($oids);
    if ($oids) {
        echo 'Dell ';
        foreach (explode("\n", $oids) as $data) {
            list($oid,)    = explode(' ', $data, 2);
            $state_oid     = '4.1';
            $state_current = snmp_get($device, $main_oid.$state_oid, '-Oevq');
            discover_sensor($valid['sensor'], 'state', $device, $main_oid.$state_oid, "virtualDiskState.$state_oid", 'dell', 'Raid State', '1', '1', null, null, null, null, $state_current);
        }
    }
}
