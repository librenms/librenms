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
    'Total Counter' => ['.1.3.6.1.4.1.18334.1.1.1.5.7.2.1.1', 'bwLargetotalCounter'],
'Total Scans Large' => ['.1.3.6.1.4.1.18334.1.1.1.5.7.2.1.2','totalScansLargeCounter'],
'Total Duplex' =>      ['.1.3.6.1.4.1.18334.1.1.1.5.7.2.1.3','totalDuplexCounter'],
//'Total Unknown ché pas cé koi' =>      ['.1.3.6.1.4.1.18334.1.1.1.5.7.2.1.4','totalUnknownCounter'],
'Total Scans' =>      ['.1.3.6.1.4.1.18334.1.1.1.5.7.2.1.5','totalScansCounter'],
'Total Copy Black' =>      ['.1.3.6.1.4.1.18334.1.1.1.5.7.2.1.6','totalCopyBlackCounter'],
'Total Large Bi-Color' =>      ['.1.3.6.1.4.1.18334.1.1.1.5.7.2.1.7','totalLargeBiColorCounter'],
'Total Originals' =>      ['.1.3.6.1.4.1.18334.1.1.1.5.7.2.1.8','totalOriginalsCounter'],
'Total Sheets' =>      ['.1.3.6.1.4.1.18334.1.1.1.5.7.2.1.9','totalSheetsCounter'],
'Total Printed Sheets' =>      ['.1.3.6.1.4.1.18334.1.1.1.5.7.2.1.10','totalDuplexPrintedSheetsCounter'],
];

foreach ($session_rate as $descr => $oid) {
    $oid_num = $oid[0];
    $oid_txt = $oid[1];
    $result = snmp_getnext($device, $oid_num, '-Ovq', '');
//    $result = str_replace(' Sessions Per Second', '', $result);

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
