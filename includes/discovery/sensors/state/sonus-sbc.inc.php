<?php

/**
 * sonus-sbc.inc.php
 *
 * LibreNMS state sensor discovery module for Sonus SBC
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
 *
 * @copyright  2026 Network Factory Solutions
 * @author     Sofia El Khalifi <sofia.elkhalifi@netsf.fr>
 */
$deviceModel = DeviceCache::get($device['device_id']);

$stateName = 'sonusSystemServerStatusMgmtRedundancyRole';

$states = [
    ['value' => 1, 'generic' => 2, 'descr' => 'Unknown'],
    ['value' => 2, 'generic' => 1, 'descr' => 'standby'],
    ['value' => 3, 'generic' => 0, 'descr' => 'active'],
    ['value' => 4, 'generic' => 1, 'descr' => 'switchoverPending'],
];

create_state_index($stateName, $states);

$server_status = SnmpQuery::device($deviceModel)->numeric()->walk('.1.3.6.1.4.1.2879.2.8.5.1.12.1.8')->values();

foreach ($server_status as $key => $value) {
    $index = explode('.1.8.', $key);
    $server_name = SnmpQuery::get('.1.3.6.1.4.1.2879.2.8.5.1.12.1.16.' . $index[1])->value();
    $descr = 'Redundancy role: ' . $server_name;
    $sensor_value = (int) $value;

    discover_sensor(
        null,
        'state',
        $device,
        $key,
        $server_name,
        $stateName,
        $descr,
        1,
        1,
        null,
        null,
        null,
        null,
        $sensor_value,
        'snmp',
        null,
        null,
        null,
        'Server redundancy Role'
    );
}

unset(
    $server_status,
    $server_name,
    $stateName,
    $descr,
    $states,
    $entry
);
