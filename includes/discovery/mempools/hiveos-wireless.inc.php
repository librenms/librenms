<?php
/**
 * hiveos-wireless.inc.php
 *
 * AeroHive Hiveos-Wireless Discovery Module
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
 * @copyright  2018 Ryan Finney
 * @author     https://github.com/theherodied/
 */
if ($device['os'] == 'hiveos-wireless') {
    echo 'Hiveos-Wireless : ';
    $memory_oid = '1.3.6.1.4.1.26928.1.2.4.0';
    $usage = snmp_get($device, $memory_oid, '-Ovq');
    if (is_numeric($usage)) {
        discover_mempool($valid_mempool, $device, '0', 'hiveos-wireless', 'Memory');
    }
}
