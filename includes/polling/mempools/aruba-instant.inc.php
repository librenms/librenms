<?php
/**
 * aruba-instant.inc.php
 *
 * LibreNMS mempools polling module for Aruba Instant
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
 * @copyright  2019 Timothy Willey
 * @author     Timothy Willey <developer@timothywilley.net>
 */
echo 'aruba-instant-MEMORY-POOL: ';

$memory_pool_total = snmpwalk_group($device, 'aiAPTotalMemory', 'AI-AP-MIB');
$memory_pool_free  = snmpwalk_group($device, 'aiAPMemoryFree', 'AI-AP-MIB');
$ap_serial_numbers = snmpwalk_group($device, 'aiAPSerialNum', 'AI-AP-MIB');

foreach ($memory_pool_total as $index => $entry) {
    if ($entry['aiAPTotalMemory']) {
        d_echo($ap_serial_numbers[$index]['aiAPSerialNum'].' '.$entry['aiAPTotalMemory'].' / '.$memory_pool_free[$index]['aiAPMemoryFree'].PHP_EOL);

        $total     = $entry['aiAPTotalMemory'];
        $free      = $memory_pool_free[$index]['aiAPMemoryFree'];
        $used      = $total - $free;
        $perc      = ($used / $total * 100);

        $mempool['total'] = $total;
        $mempool['used']  = $used;
        $mempool['free']  = $free;
        $mempool['perc']  = $perc;
    } //end if
} //end foreach
