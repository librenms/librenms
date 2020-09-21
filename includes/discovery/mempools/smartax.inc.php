<?php
/**
 * smartax.inc.php
 *
 * LibreNMS mempool discovery module for Huawei SmartAX
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
 * @link       http://librenms.org
 * @copyright  2020 Jozef Rebjak
 * @author     Jozef Rebjak <jozefrebjak@icloud.com>
 */
if ($device['os'] === 'smartax') {
    $slotindex = snmpwalk_cache_oid($device, 'hwSlotIndex', [], 'HWMUSA-DEV-MIB', 'huawei');
    $slotdesc = snmpwalk_cache_oid($device, 'hwMusaBoardSlotDesc', [], 'HWMUSA-DEV-MIB', 'huawei');
    $data = snmpwalk_cache_oid($device, 'hwMusaBoardRamUseRate', [], 'HWMUSA-DEV-MIB', 'huawei');
    foreach ($data as $index => $item) {
        if (is_numeric($item['hwMusaBoardRamUseRate']) && $item['hwMusaBoardRamUseRate'] != -1) {
            $string = 'Slot';
            $number = $slotindex[$index]['hwSlotIndex'];
            $boarddescr = $slotdesc[$index]['hwMusaBoardSlotDesc'];
            $descr = implode(' ', [$string, $number, $boarddescr]);
            discover_mempool($valid_mempool, $device, $index, 'smartax', $descr, '1');
        }
    }
}
unset(
    $data,
    $descr,
    $index,
    $item
);
