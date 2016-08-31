<?php
/*
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
*/
if ($device['os'] == 'cambium') {
    echo 'Cambium : ';

    $descr = 'Processor';
    $usage = str_replace('"', "", snmp_get($device, 'CAMBIUM-PMP80211-MIB::sysCPUUsage.0', '-OvQ'));

    if (is_numeric($usage)) {
        discover_processor(
            $valid['processor'],
            $device,
            'CAMBIUM-PMP80211-MIB::sysCPUUsage.0',
            '0',
            'cambium-cpu',
            $descr,
            '100',
            $usage,
            null,
            null
        );
    }
}
