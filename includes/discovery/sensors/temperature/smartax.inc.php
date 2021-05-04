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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 * @copyright  2018 TheGreatDoc
 * @author     TheGreatDoc
 */
$temp_oid = '1.3.6.1.4.1.2011.2.6.7.1.1.2.1.10.0';
$descr_oid = '1.3.6.1.4.1.2011.2.6.7.1.1.2.1.7.0';

$data = snmpwalk_array_num($device, $temp_oid);
$descr_data = snmpwalk_array_num($device, $descr_oid);

$data = reset($data);
$descr_data = reset($descr_data);

foreach ($data as $index => $value) {
    if ($value < '999') {
        $tempCurr = $value;
        $temperature_oid = '.' . $temp_oid . '.' . $index;
        $descr = $descr_data[$index];
        discover_sensor($valid['sensor'], 'temperature', $device, $temperature_oid, $index, 'smartax', $descr, '1', '1', null, null, null, null, $tempCurr);
    }
}
