<?php
/*
 * LibreNMS MIB-based discovery
 *
 * Copyright (c) 2015 Gear Consulting Pty Ltd <github@libertysys.com.au>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

/*
 * The overall plan for MIB-based support is:
 *
 * Discovery:
 *  1. SNMP_get sysObjectID; look for a MIB matching this object (set_os_mib).
 *  2. Add any extra MIBs that should be there for a given device
 *     (includes/discovery/os/*.inc.php).
 *  3. Walk these MIBs to see if they exist in the device (this file).
 *     Save the ones that do in the database table device_oids.
 *
 * Polling:
 *  5. For each MIB in the device_oids table, walk the device for that MIB.
 *  6. Save each MIB value in its own RRD file.  (At present there is no
 *     deletion of values that disappear.)
 *
 * Graphing:
 *  7. For each MIB in the device_oids table, create a graph from the RRD
 *     file.  All graphs go into the MIB section at present.
 */

set_os_mib($device);
$mibs = array();

// remove any existing device_oids for this device
dbDelete('device_oids', 'device_id = ?', array($device['device_id']));

// parse MIBs and check for them on the device
foreach ($device['mibs'] as $name => $module) {
    d_echo("MIB discovery: $name, $module");
    $mibs[$name] = snmp_mib_load($name, $module);
    $oids = snmpwalk_cache_oid($device, "$module::$name", array(), $module);

    // add the oids for this device
    foreach ($oids[0] as $key => $val) {
        $data = $mibs[$name][$key];
        $data['device_id'] = $device['device_id'];
        $result = dbInsert($data, 'device_oids');
        d_echo("dbInsert for $name $key returned $result");
    }
}
