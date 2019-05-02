<?php
/*
 * LibreNMS Dantel Webmon generic sensor
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * @package    LibreNMS
 * @subpackage webui
 * @link       http://librenms.org
 * @copyright  2019 LibreNMS
 * @author     LibreNMS Contributors
 */

$session_rate = [
    'Sessions/sec 10m avg' => '.1.3.6.1.4.1.12356.101.4.1.12.0',  //FORTINET-FORTIGATE-MIB::fgSysSesRate10.0
    'Sessions/sec 60m avg' => '.1.3.6.1.4.1.12356.101.4.1.14.0',  //FORTINET-FORTIGATE-MIB::fgSysSesRate60.0
];

$index = 0;
foreach ($session_rate as $descr => $oid) {
    $result = snmp_get($device, $oid, '-Ovq');
    $result = str_replace(' Sessions Per Second', '', $result);

    discover_sensor(
        $valid['sensor'],
        'count',
        $device,
        $oid,
        $index,
        'sessions',
        $descr,
        1,
        1,
        null,
        null,
        null,
        null,
        $result
    );
    $index++;
}

$oid = '.1.3.6.1.4.1.12356.101.4.1.8.0'; //FORTINET-FORTIGATE-MIB::fgSysSesCount.0
$result = snmp_get($device, $oid, '-Ovq');
discover_sensor(
    $valid['sensor'],
    'count',
    $device,
    $oid,
    $index,
    'sessions',
    'Current sessions',
    1,
    1,
    null,
    null,
    null,
    null,
    $result
);
$index++;
