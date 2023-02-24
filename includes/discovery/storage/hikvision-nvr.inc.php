<?php
/**
 * hikvision-nvr.inc.php
 *
 * LibreNMS storage discovery module for hikvision-nvr
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
 * @copyright  2019 Spencer Butler
 * @author     Spencer Butler <github@crooked.app>
 */
if ($device['os'] === 'hikvision-nvr') {
    echo 'hikvision-nvr:';

    $size = snmp_get($device, 'hikDiskCapability.1.0', '-Ovq', 'HIKVISION-MIB');
    $free = snmp_get($device, 'hikDiskFreeSpace.1.0', '-Ovq', 'HIKVISION-MIB');
    $index = 0;
    $type = 'hikvision-nvr';
    $descr = 'Storage';
    $mib = 'HIKVISION-MIB';
    discover_storage($valid_storage, $device, $index, $type, $mib, $descr, 0, size, null);
}
