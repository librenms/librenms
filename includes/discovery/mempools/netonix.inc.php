<?php
/**
 * netonix.inc.php
 *
 * LibreNMS mempools module for Netonix
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
 * @copyright  2016 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

if ($device['os'] == 'netonix') {
    echo 'NETONIX : ';

    $free = snmp_get($device, 'UCD-SNMP-MIB::memTotalFree.0', '-OvQU');

    if (is_numeric($free)) {
        discover_mempool($valid_mempool, $device, 0, 'netonix', 'Memory', 1024);
    }
}
