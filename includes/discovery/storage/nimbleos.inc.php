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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2018 theherodied
 * @author     https://github.com/theherodied/
 */
use LibreNMS\Config;

$nimble_storage = snmpwalk_cache_oid($device, 'volEntry', null, 'NIMBLE-MIB');
if (is_array($nimble_storage)) {
    echo 'volEntry ';
    foreach ($nimble_storage as $index => $storage) {
        $units  = 1024*1024;
        $fstype = $storage['volOnline'];
        $descr  = $storage['volName'];
        $size = $storage['volSizeLow'] * $units;
        $used = $storage['volUsageLow'] * $units;
        if (is_numeric($index)) {
            discover_storage($valid_storage, $device, $index, $fstype, 'nimbleos', $descr, $size, $units, $used);
        }
        unset($deny, $fstype, $descr, $size, $used, $units, $storage_rrd, $old_storage_rrd, $hrstorage_array);
    }
}
