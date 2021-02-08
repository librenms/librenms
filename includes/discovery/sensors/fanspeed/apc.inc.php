<?php
/**
 * apc.inc.php
 *
 * LibreNMS os sensor fanspeeds module for APC
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
 * @copyright  2016 Neil Lathwood
 * @author     Neil Lathwood <neil@lathwood.co.uk>
 */
foreach ($pre_cache['cooling_unit_analog'] as $index => $data) {
    $cur_oid = '.1.3.6.1.4.1.318.1.1.27.1.4.1.2.1.3.' . $index;
    $descr = $data['coolingUnitStatusAnalogDescription'];
    $scale = $data['coolingUnitStatusAnalogScale'];
    $value = $data['coolingUnitStatusAnalogValue'];
    if (preg_match('/Fan Speed/', $descr) && $data['coolingUnitStatusAnalogUnits'] == '%' && $value >= 0) {
        discover_sensor($valid['sensor'], 'fanspeed', $device, $cur_oid, $cur_oid, 'apc', $descr, $scale, 1, null, null, null, null, $value);
    }
}
