<?php
/*
 * LibreNMS support for kyocera print counters
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2019 LibreNMS
 * @author     Teura ORBECK
 */

 /*
.1.3.6.1.4.1.11.2.3.9.4.2.1.4.1.2.5.0 = INTEGER: 14904
*/
$session_rate = [
    'Total Prints'=>['hp', '.1.3.6.1.4.1.11.2.3.9.4.2.1.4.1.2.5.0', 'printTotal', 'Print']
];

foreach ($session_rate as $descr => $oid) {
    $vendorRef = $oid[0];
    $oid_num = $oid[1];
    $oid_ref = $oid[2];
    $group = $oid[3];
    $result = snmp_get($device, $oid_num, '-Ovq');
    if ($result > 0) {
        discover_sensor(
        $valid['sensor'],
        'count',
        $device,
        $oid_num,
        $oid_ref . '.0',
        $vendorRef,
        $descr,
        1,
        1,
        null,
        null,
        null,
        null,
        $result
    );
    }
}

