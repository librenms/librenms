<?php
/**
 * ipolis.inc.php
 *
 * LibreNMS os poller module for Hanwha Techwin devices
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
 * @copyright  2018 Priit Mustasaar
 * @author     Priit Mustasaar <priit.mustasaar@gmail.com>
 */
$oids = [
    'hardware' => $device['sysObjectID'] . '.1.0',
    'version' => $device['sysObjectID'] . '.2.1.1.0',
];

$os_data = snmp_get_multi_oid($device, $oids);

foreach ($oids as $var => $oid) {
    $$var = trim($os_data[$oid], '"');
}

unset($oids, $os_data);
