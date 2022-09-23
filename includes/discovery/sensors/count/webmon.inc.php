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
 * @link       https://www.librenms.org
 * @copyright  2019 Mike Williams
 * @copyright  2019 PipoCanaja
 * @author     Mike Williams <mike@mgww.net>
 * @author     PipoCanaja <PipoCanaja@gmail.com>
 */

$prefixes = [
    'pSlot1' => '.1.3.6.1.4.1.994.3.4.7.18.1.66.',
    'pSlot2' => '.1.3.6.1.4.1.994.3.4.7.19.1.66.',
    'pSlot3' => '.1.3.6.1.4.1.994.3.4.7.20.1.66.',
    'pSlot4' => '.1.3.6.1.4.1.994.3.4.7.21.1.66.',
    'pSlot5' => '.1.3.6.1.4.1.994.3.4.7.22.1.66.',
    'pSlot6' => '.1.3.6.1.4.1.994.3.4.7.23.1.66.',
    'pOnboardSensor' => '.1.3.6.1.4.1.994.3.4.7.6.1.66.',
];

foreach ($prefixes as $prefix => $numOidPrefix) {
    $walk = snmpwalk_cache_oid($device, $prefix . 'Table', [], 'WEBMON-EDGE-MATRIX-MIB');

    foreach ($walk as $index => $oid) {
        if ($oid[$prefix . 'Configured'] != '0' && $oid[$prefix . 'SensorType'] != 'humidity' && $oid[$prefix . 'SensorType'] != 'temperature' && $oid[$prefix . 'LiveRaw']) {
            $num_oid = $numOidPrefix . $index;
            $descr = $oid[$prefix . 'Description'];
            $group = $prefix;
            $value = $oid[$prefix . 'LiveRaw'];
            $lowLimit = $oid[$prefix . 'Thresh4'];
            $lowWarnLimit = $oid[$prefix . 'Thresh3'];
            $highLimit = $oid[$prefix . 'Thresh1'];
            $highWarnLimit = $oid[$prefix . 'Thresh2'];
            if ($oid[$prefix . 'Units']) {
                $descr .= '(' . $oid[$prefix . 'Units'] . ')';
            }
            discover_sensor($valid['sensor'], 'count', $device, $num_oid, $prefix . 'LiveRaw.' . $index, 'webmon', $descr, '1', '1', $lowLimit, $lowWarnLimit, $highWarnLimit, $highLimit, $value, 'snmp', null, null, null, $group);
        }
    }
}
