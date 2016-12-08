<?php
/*
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */
echo 'Cambium CPU Usage';

if ($device['os'] == 'cambium') {
    $usage = str_replace('"', "", snmp_get($device, 'CAMBIUM-PMP80211-MIB::sysCPUUsage.0', '-OvQ'));

    if (is_numeric($usage)) {
        $proc = ($usage / 10);
    }
}
