<?php
/**
 * websensor.inc.php
 *
 * LibreNMS humidity sensor discovery module for Comet System Web Sensor
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
if (is_numeric($pre_cache['websensor_valuesInt']['humInt.0'])) {
    $humidity_oid = '.1.3.6.1.4.1.22626.1.2.3.2.0';
    $humidity_index = 'humInt.0';
    $descr = 'Humidity';
    $humidity = $pre_cache['websensor_valuesInt']['humInt.0'] / 10;
    $high_limit = $pre_cache['websensor_settings']['humHighInt.0'] / 10;
    $low_limit = $pre_cache['websensor_settings']['humLowInt.0'] / 10;
    discover_sensor($valid['sensor'], 'humidity', $device, $humidity_oid, $humidity_index, 'websensor', $descr, '10', '1', $low_limit, null, null, $high_limit, $humidity);
}
