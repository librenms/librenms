<?php
/*
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 * @author     rhinoau
 */

//portName OID is based on each device model/MIB
$oid = $device['sysObjectID'] . '.1.9.1.1.7';

$port_names = snmpwalk_cache_oid($device, $oid, $port_names);

foreach ($port_names as $oid => $value) {
    if (! empty($value['iso'])) {
        // determine index
        $index = end(explode('.', $oid));
        $port_stats[$index]['ifAlias'] = $value['iso'];
    }
}
