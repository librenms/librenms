<?php
/**
 * smartax.inc.php
 *
 * LibreNMS power discovery module for Huawei SmartAX
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
 * @copyright  2018 TheGreatDoc
 * @author     TheGreatDoc
 */
$power_frame_oid = '.1.3.6.1.4.1.2011.2.6.7.1.1.1.1.11.0';

$power = snmp_get($device, $power_frame_oid, '-Ovq');
$index = '0';

discover_sensor($valid['sensor'], 'power', $device, $power_frame_oid, $index, 'smartax-total', 'Chassis Total', '1', '1', null, null, null, null, $power);

$power_oid = '1.3.6.1.4.1.2011.2.6.7.1.1.2.1.11.0';
$descr_oid = '1.3.6.1.4.1.2011.2.6.7.1.1.2.1.7.0';

$data = snmpwalk_array_num($device, $power_oid);
$descr_data = snmpwalk_array_num($device, $descr_oid);

$data = reset($data);
$descr_data = reset($descr_data);

foreach ($data as $index => $value) {
    $powerCurr = $value;
    $pow_oid = '.' . $power_oid . '.' . $index;
    $descr = $descr_data[$index];
    discover_sensor($valid['sensor'], 'power', $device, $pow_oid, $index, 'smartax', $descr, '1', '1', null, null, null, null, $powerCurr);
}
