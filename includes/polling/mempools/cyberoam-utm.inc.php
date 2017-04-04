<?php
/**
 * cyberoam-utm.inc.php
 *
 * LibreNMS mempools poller module for Cyberoam-UTM
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

if ($mempool['mempool_index'] == 0) {
    $mem_perc = snmp_get($device, '.1.3.6.1.4.1.21067.2.1.2.4.2.0', '-OvQ');
    $mem_capacity = snmp_get($device, '.1.3.6.1.4.1.21067.2.1.2.4.1.0', '-OvQ');
    $mem_capacity = ($mem_capacity*1024*1024);
    $mempool['total'] = $mem_capacity;
    $mempool['used']  = ($mem_capacity / 100) * $mem_perc;
    $mempool['free']  = $mempool['total'] - $mempool['used'];
}

if ($mempool['mempool_index'] == 1) {
    $swap_perc = snmp_get($device, '.1.3.6.1.4.1.21067.2.1.2.4.4.0', '-OvQ');
    $swap_capacity = snmp_get($device, '.1.3.6.1.4.1.21067.2.1.2.4.3.0', '-OvQ');
    $swap_capacity = ($swap_capacity*1024*1024);
    $mempool['total'] = $swap_capacity;
    $mempool['used']  = ($swap_capacity / 100) * $swap_perc;
    $mempool['free']  = $mempool['total'] - $mempool['used'];
}

unset(
    $mem_perc,
    $mem_capacity,
    $swap_perc,
    $swap_capacity
);
