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
    'Session Count' => ['.1.3.6.1.4.1.12356.105.1.10', 'fmlSysSesCount'],  //FORTINET-FORTIMAIL-MIB::fmlSysSesCount.0
	'Deferred Queue' => ['.1.3.6.1.4.1.12356.105.1.103.1', 'fmlMailOptionsDeferQueue'],  //FORTINET-FORTIMAIL-MIB::fmlMailOptionsDeferQueue.0
];

foreach ($session_rate as $descr => $oid) {
    $oid_num = $oid[0];
    $oid_txt = $oid[1];
    $result = snmp_getnext($device, $oid_txt, '-Ovq', 'FORTINET-FORTIMAIL-MIB');
    $result = str_replace(' Sessions Per Second', '', $result);

    discover_sensor(
        $valid['sensor'],
        'count',
        $device,
        $oid_num . '.0',
        $oid_txt . '.0',
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
}
