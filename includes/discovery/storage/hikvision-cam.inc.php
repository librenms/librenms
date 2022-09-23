<?php
/**
 * hikvision-cam.inc.php
 *
 * LibreNMS storage discovery module for hikvision-cam
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
if ($device['os'] === 'hikvision-cam') {
    echo 'hikvision-cam:';

    $size = snmp_get($device, 'diskSize.0', '-Ovq', 'HIK-DEVICE-MIB');
    $used = snmp_get($device, 'diskPercent.0', '-Ovq', 'HIK-DEVICE-MIB');
    $index = 0;
    $fstype = 'hikvision-cam';
    $mib = 'HIK-DEVICE-MIB';
    $descr = 'Storage';
    discover_storage($valid_storage, $device, $index, $fstype, $mib, $descr, $size, null, $used);
}
