<?php
/**
 * netbotz.inc.php
 *
 * LibreNMS airflow discovery module for Netbotz
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
 * @copyright  2017 Neil Lathwood
 * @author     Neil Lathwood <neil@lathwood.co.uk>
 */

d_echo($pre_cache['netbotz_airflow']);

if (is_array($pre_cache['netbotz_airflow'])) {
    echo 'NetBotz ';
    foreach ($pre_cache['netbotz_airflow'] as $index => $data) {
        if ($data['airFlowSensorValue']) {
            $divisor = 10;
            $multiplier = 1;
            $value = $data['airFlowSensorValue'] / $divisor;
            $oid = '.1.3.6.1.4.1.5528.100.4.1.5.1.2.' . $index;
            $index = 'airFlowSensorValue.' . $index;
            $descr = $data['airFlowSensorLabel'];
            if (is_numeric($value)) {
                discover_sensor($valid['sensor'], 'airflow', $device, $oid, $index, 'netbotz', $descr, $divisor, $multiplier, null, null, null, null, $value);
            }
        }
    }
}
