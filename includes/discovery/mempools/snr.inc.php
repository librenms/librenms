<?php
/***
 * snr.inc.php
 *
 * LibreNMS mempool discovery module for snr
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
 * 
 * @author     hartred
 */

if ($device['os'] == 'snr') {
    $usage = snmp_get($device, 'sysMemoryUsage.1', '-OvQ', 'NAG-MIB');
    if (is_numeric($usage)) {
        discover_mempool($valid_mempool, $device, 0, 'snr', 'Memory Usage', '1', null, null);
    }
}
