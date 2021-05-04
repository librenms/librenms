<?php
/**
 * nimbleos.inc.php
 *
 * LibreNMS storage polling module for Nimble Storage
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
if (! is_array($storage_cache['nimbleos'])) {
    $storage_cache['nimbleos'] = snmpwalk_cache_oid($device, 'volEntry', null, 'NIMBLE-MIB');
    d_echo($storage_cache);
}
$entry = $storage_cache['nimbleos'][$storage['storage_index']];
$storage['units'] = 1024 * 1024;
//nimble uses a high 32bit counter and a low 32bit counter to make a 64bit counter
$storage['size'] = (($entry['volSizeHigh'] << 32) + $entry['volSizeLow']) * $storage['units'];
$storage['used'] = (($entry['volUsageHigh'] << 32) + $entry['volUsageLow']) * $storage['units'];
$storage['free'] = ($storage['size'] - $storage['used']);
