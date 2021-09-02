<?php
/**
 * compas.inc.php
 *
 * LibreNMS runtime sensor discovery module for Alpha Comp@s UPS
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
 * @copyright  2019 Paul Parsons
 * @author     Paul Parsons <paul@cppmonkey.net>
 */
$batteryAutonomy = snmp_get($device, 'es1dc1DataBatCalculatedAutonomy.0', '-Ovqe', 'SITE-MONITORING-MIB');
$curOID = '.1.3.6.1.4.1.26854.3.2.1.20.1.20.1.13.3.92.0';
$index = 0;

if (is_numeric($batteryAutonomy)) {
    $sensorType = 'compas';
    $descr = 'Runtime';
    discover_sensor($valid['sensor'], 'runtime', $device, $curOID, $index, $sensorType, $descr, '1', '1', null, null, null, null, $batteryAutonomy);
}
