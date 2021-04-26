<?php
/**
 * nimbleos.inc.php
 *
 * LibreNMS storage discovery module for Nimble Storage
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
 * @copyright  2018 Ryan Finney
 * @author     https://github.com/theherodied/
 */
use LibreNMS\Config;

if ($device['os'] == 'nimbleos') {
    $nimble_storage = snmpwalk_cache_oid($device, 'volEntry', null, 'NIMBLE-MIB');
    if (is_array($nimble_storage)) {
        echo 'volEntry ';
        foreach ($nimble_storage as $index => $storage) {
            $units = 1024 * 1024;
            $fstype = $storage['volOnline'];
            $descr = $storage['volName'];
            //nimble uses a high 32bit counter and a low 32bit counter to make a 64bit counter
            $size = (($storage['volSizeHigh'] << 32) + $storage['volSizeLow']) * $units;
            $used = (($storage['volUsageHigh'] << 32) + $storage['volUsageLow']) * $units;
            if (is_numeric($index)) {
                discover_storage($valid_storage, $device, $index, $fstype, 'nimbleos', $descr, $size, $units, $used);
            }
            unset($deny, $fstype, $descr, $size, $used, $units, $storage_rrd, $old_storage_rrd, $hrstorage_array);
        }
    }
}
