<?php

/**
 * raritan-pdu.inc.php
 *
 * LibreNMS temperature sensor discovery module for Raritan
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2017 Neil Lathwood
 * @author     Neil Lathwood <gh+n@laf.io>
 */
$descr = 'Processor Humidity';
$divisor = 1;
$multiplier = '1';

d_echo('Humidity for Raritan PDU');
$oids = snmpwalk_cache_multi_oid($device, 'externalSensorTable', [], 'PDU-MIB');
$offset = 0;
foreach ($oids as $index => $sensor) {
    if ($sensor['externalSensorType'] == 'humidity') {
        $oid = ".1.3.6.1.4.1.13742.4.3.3.1.41.$index";
        $descr = $sensor['externalSensorName'];
        $hum_current = $sensor['externalSensorValue'];
        $limit_high = $sensor['externalSensorUpperWarningThreshold'] / $divisor;
        $limit_low = $sensor['externalSensorLowerWarningThreshold'] / $divisor;
        $limit_high_warn = $sensor['externalSensorUpperCriticalThreshold'] / $divisor;
        $limit_low_warn = $sensor['externalSensorLowerCriticalThreshold'] / $divisor;
        $offset++;
        if (is_numeric($hum_current) && $hum_current >= 0) {
            discover_sensor(null, 'humidity', $device, $oid, $offset, 'raritan', $descr, $divisor, $multiplier, $limit_low, $limit_low_warn, $limit_high_warn, $limit_high, $hum_current);
        }
    }
}
