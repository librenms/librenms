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

foreach (SnmpQuery::walk($oid)->values() as $oid => $name) {
    if ($name) {
        $index = \Illuminate\Support\Str::afterLast($oid, '.');
        $port_stats[$index]['ifAlias'] = $name;
    }
}
