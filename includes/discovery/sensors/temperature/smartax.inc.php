<?php
/**
 * smartax.inc.php
 *
 * LibreNMS temperature discovery module for Huawei SmartAX
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2018 TheGreatDoc
 * @author     TheGreatDoc
 */

$data = snmpwalk_group($device, 'enterprises.2011.2.6.7.1.1.2.1.10.0', 'SNMPv2-SMI');

foreach ($data as $index => $entry) {
    $tempCurr = $entry;
    $index = substr(strrchr($index, '.'), 1);
    $temperature_oid  = "enterprises.2011.2.6.7.1.1.2.1.10.0.$index";
    $descr = snmp_get($device, '1.3.6.1.4.1.2011.2.6.7.1.1.2.1.7.0.' . $index, '-Oqv');
    discover_sensor($valid['sensor'], 'temperature', $device, $temperature_oid, $index, 'smartax', $descr, '1', '1', null, null, null, null, $tempCurr);
}
