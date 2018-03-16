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
if (!is_array($storage_cache['nimbleos'])) {
    $storage_cache['nimbleos'] = snmpwalk_cache_oid($device, 'volEntry', null, 'NIMBLE-MIB');
    d_echo($storage_cache);
}
$entry = $storage_cache['nimbleos'][$storage[storage_index]];
$storage['units'] = 1024*1024;
$storage['size'] = ($entry['volSizeLow'] * $storage['units']);
$storage['used'] = ($entry['volUsageLow'] * $storage['units']);
$storage['free'] = ($storage['size'] - $storage['used']);
