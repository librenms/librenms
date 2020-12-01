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
KYOCERA
.1.3.6.1.4.1.1347.42.2.5.1.1.1.1 = INTEGER: 1176
.1.3.6.1.4.1.1347.42.2.5.1.1.2.1 = INTEGER: 177
.1.3.6.1.4.1.1347.42.2.5.1.1.3.1 = INTEGER: 1353
.1.3.6.1.4.1.1347.42.2.1.1.1.6.1.1
*/
$session_rate = [
    'Print duplex front pages'=>['kyocera','.1.3.6.1.4.1.1347.42.2.5.1.1.1.1','printDuplexFront','Print'],
    'Print duplex rear pages'=>['kyocera','.1.3.6.1.4.1.1347.42.2.5.1.1.2.1','printDuplexRear','Print'],
    'Total Prints'=>['kyocera','.1.3.6.1.4.1.1347.42.2.1.1.1.6.1.1','printTotal','Print']
];

foreach ($session_rate as $descr => $oid) {
    $vendorRef = $oid[0];
    $oid_num = $oid[1];
    $oid_ref = $oid[2];
    $group = $oid[3];
    $result = snmp_get($device, $oid_num,'-Ovq');
    if($result>0) {
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

