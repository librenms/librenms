<?php
/**
 * powerlogic.inc.php
 *
 * LibreNMS power sensor discovery module for Schneider PowerLogic
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
 * @author     Neil Lathwood <neil@lathwood.co.uk>
 */
$data = $pre_cache['powerlogic_powerTable'];

if (is_numeric($data['pReal'][1])) {
    $current_oid = '.1.3.6.1.4.1.3833.1.7.255.15.1.1.3.1.2.1';
    $index = 'pReal';
    $descr = 'Power';
    $multiplier = 1000;
    $current = ($data['pReal'][1] * $multiplier);
    discover_sensor($valid['sensor'], 'power', $device, $current_oid, $index, 'powerlogic', $descr, 1, $multiplier, null, null, null, null, $current);
}

unset($data);
