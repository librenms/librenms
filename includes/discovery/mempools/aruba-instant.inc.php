<?php
/**
 * aruba-instant.inc.php
 *
 * LibreNMS mempools discovery module for Aruba Instant
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
if ($device['os'] === 'aruba-instant') {
    echo 'aruba-instant-MEMORY-POOL: ';

    $memory_pool_total = snmpwalk_group($device, 'aiAPTotalMemory', 'AI-AP-MIB');
    $memory_pool_free  = snmpwalk_group($device, 'aiAPMemoryFree', 'AI-AP-MIB');
    $ap_serial_numbers = snmpwalk_group($device, 'aiAPSerialNum', 'AI-AP-MIB');

    foreach ($memory_pool_total as $index => $entry) {
        if ($entry['aiAPTotalMemory']) {
            d_echo($ap_serial_numbers[$index]['aiAPSerialNum'].' '.$entry['aiAPTotalMemory'].' / '.$memory_pool_free[$index]['aiAPMemoryFree'].PHP_EOL);

            $oid_index = '';
            $macparts = explode(':', $index);
            foreach ($macparts as $part) {
                $oid_index .= hexdec($part).'.';
            }
            $oid_index = rtrim($oid_index, '.');

            $combined_oid = sprintf(
                '%s::%s.%s',
                'AI-AP-MIB',
                'aiAPTotalMemory',
                $oid_index
            );
            $usage_oid = snmp_translate($combined_oid, 'ALL', 'arubaos', '-On', null);

            $descr     = $ap_serial_numbers[$index]['aiAPSerialNum'];
            $total     = $entry['aiAPTotalMemory'];
            $free      = $memory_pool_free[$index]['aiAPMemoryFree'];
            $used      = $total - $free;
            $perc      = ($used / $total * 100);

            discover_mempool($valid_mempool, $device, $descr, 'aruba-instant', $descr, '1', null, null);
        } //end if
    } //end foreach
} // end if
