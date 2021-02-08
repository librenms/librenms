<?php
/**
 * sentry4.inc.php
 *
 * LibreNMS humidity discovery module for Sentry4
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
 * @copyright  2017 Neil Lathwood
 * @author     Neil Lathwood <gh+n@laf.io>
 */
foreach ($pre_cache['sentry4_humid'] as $index => $data) {
    $descr = $data['st4HumidSensorName'];
    $oid = ".1.3.6.1.4.1.1718.4.1.10.3.1.1.$index";
    $low_limit = $data['st4HumidSensorLowAlarm'];
    $low_warn_limit = $data['st4HumidSensorLowWarning'];
    $high_limit = $data['st4HumidSensorHighAlarm'];
    $high_warn_limit = $data['st4HumidSensorHighWarning'];
    $current = $data['st4HumidSensorValue'];
    if ($current >= 0) {
        discover_sensor($valid['sensor'], 'humidity', $device, $oid, "st4HumidSensorValue.$index", 'sentry4', $descr, 1, 1, $low_limit, $low_warn_limit, $high_warn_limit, $high_limit, $current);
    }
}
