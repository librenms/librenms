<?php
/**
 * cyberoam-utm.inc.php
 *
 * LibreNMS mempools discovery module for Cyberoam-UTM
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

if ($device['os'] === 'cyberoam-utm') {
    echo 'Cyberoam UTM: ';

    $mem_perc = snmp_get($device, '.1.3.6.1.4.1.21067.2.1.2.4.2.0', '-OvQ');
    $mem_capacity = snmp_get($device, '.1.3.6.1.4.1.21067.2.1.2.4.1.0', '-OvQ');

    if ((is_numeric($mem_perc)) && (is_numeric($mem_capacity))) {
        discover_mempool($valid_mempool, $device, 0, 'cyberoam-utm', 'Memory', '1', null, null);
    }

    $swap_perc = snmp_get($device, '.1.3.6.1.4.1.21067.2.1.2.4.4.0', '-OvQ');
    $swap_capacity = snmp_get($device, '.1.3.6.1.4.1.21067.2.1.2.4.3.0', '-OvQ');

    if ((is_numeric($swap_perc)) && (is_numeric($swap_capacity))) {
        discover_mempool($valid_mempool, $device, 1, 'cyberoam-utm', 'Swap', '1', null, null);
    }
}

unset(
    $mem_perc,
    $mem_capacity,
    $swap_perc,
    $swap_capacity
);
