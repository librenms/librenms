<?php
/**
 * junos.inc.php

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
 *
 * @copyright  2022 Beanfield Technologies Inc
 * @author     Jeremy Ouellet <jouellet@beanfield.com>
 */
foreach ($pre_cache['junos_firewall_oids'] as $index => $firewall_entry) {
    $packetOID = genOID($firewall_entry, true);
    $bytesOID = genOID($firewall_entry, false);

    discover_sensor(
        $valid['sensor'],
        'count',
        $device,
        $packetOID,
        $index . 'Packets' . $firewall_entry['jnxFWCounterDisplayType'],
        'junos',
        $firewall_entry['jnxFWCounterDisplayName'] . ' Packets ' . $firewall_entry['jnxFWCounterDisplayType'],
        1,
        1,
        null,
        null,
        null,
        null,
        $firewall_entry['jnxFWCounterPacketCount']
    );

    discover_sensor(
        $valid['sensor'],
        'count',
        $device,
        $bytesOID,
        $index . 'Bytes' . $firewall_entry['jnxFWCounterDisplayType'],
        'junos',
        $firewall_entry['jnxFWCounterDisplayName'] . ' Bytes ' . $firewall_entry['jnxFWCounterDisplayType'],
        1,
        1,
        null,
        null,
        null,
        null,
        $firewall_entry['jnxFWCounterByteCount']
    );
}

function genOID($firewall_entry, $isPacket): string
{
    $types = [
        'other'   => 1,
        'counter' => 2,
        'policer' => 3,
        'hpolagg' => 4,
        'hpolpre' => 5,
    ];

    $oidBase = '.1.3.6.1.4.1.2636.3.5.2.1.' . ($isPacket ? '4.' : '5.');
    $suffix = '.' . $types[$firewall_entry['jnxFWCounterDisplayType']];
    $filter = $firewall_entry['jnxFWCounterDisplayFilterName'];
    $name = $firewall_entry['jnxFWCounterDisplayName'];

    //Convert strings to numerical OIDs
    $filter_oid = strlen($filter) . '.' . implode('.', unpack('c*', $filter));
    $name_oid = strlen($name) . '.' . implode('.', unpack('c*', $name));

    return $oidBase . $filter_oid . '.' . $name_oid . $suffix;
}
