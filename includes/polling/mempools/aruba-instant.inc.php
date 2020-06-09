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

$mempool_data = snmpwalk_group($device, 'aiAPSerialNum', 'AI-AP-MIB');
$mempool_data = snmpwalk_group($device, 'aiAPTotalMemory', 'AI-AP-MIB', 1, $mempool_data);
$mempool_data = snmpwalk_group($device, 'aiAPMemoryFree', 'AI-AP-MIB', 1, $mempool_data);

d_echo('$mempool_data:'.PHP_EOL);
d_echo($mempool_data);

foreach ($mempool_data as $index => $entry) {
    d_echo($entry['aiAPSerialNum'].' '.$entry['aiAPTotalMemory'].' / '.$entry['aiAPMemoryFree'].PHP_EOL);

    $total     = $entry['aiAPTotalMemory'];
    $free      = $entry['aiAPMemoryFree'];
    $used      = $total - $free;
    $perc      = ($used / $total * 100);

    $mempool['total'] = $total;
    $mempool['used']  = $used;
    $mempool['free']  = $free;
    $mempool['perc']  = $perc;
} //end foreach
