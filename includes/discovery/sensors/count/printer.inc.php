<?php
/*
 * LibreNMS
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * @link       https://www.librenms.org
*/

$walk = snmpwalk_cache_oid($device, 'prtMarkerTable', [], 'Printer-MIB');

foreach ($walk as $index => $data) {
    discover_sensor(
        $valid['sensor'],
        'count',
        $device,
        '.1.3.6.1.2.1.43.10.2.1.4.' . $index, // Printer-MIB::prtMarkerLifeCount.1.1
        'prtMarkerLifeCount',
        $device['os'],
        'Life time ' . $data['prtMarkerCounterUnit'],
        1,
        1,
        null,
        null,
        null,
        null,
        $data['prtMarkerLifeCount'],
    );

    discover_sensor(
        $valid['sensor'],
        'count',
        $device,
        '.1.3.6.1.2.1.43.10.2.1.5.' . $index, // Printer-MIB::prtMarkerPowerOnCount.1.1
        'prtMarkerPowerOnCount',
        $device['os'],
        ucfirst($data['prtMarkerCounterUnit']) . ' since powered on',
        1,
        1,
        null,
        null,
        null,
        null,
        $data['prtMarkerPowerOnCount'],
    );
}
