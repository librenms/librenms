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

use App\Models\Device;

$deviceModel = Device::find($device['device_id']);

$stateName = 'sonusSystemServerStatusMgmtRedundancyRole';

$states = [
    ['value' => 1, 'generic' => 2, 'descr' => 'Unknown'],
    ['value' => 2, 'generic' => 1, 'descr' => 'standby'],
    ['value' => 3, 'generic' => 0, 'descr' => 'active'],
    ['value' => 4, 'generic' => 1, 'descr' => 'switchoverPending'],
];

create_state_index($stateName, $states);

$server_status = SnmpQuery::device($deviceModel)->walk('.1.3.6.1.4.1.2879.2.8.5.1.12.1.8')->values();

foreach ($server_status as $index => $entry) {
    echo $index;
    var_dump($entry);
    $k_array = explode('.', (string) $index);
    echo 'k_array0  : ' . $k_array[0] . "\n";

    if ($k_array[0] == 'enterprises') {
        $ports_mapping['oid'] = str_replace('enterprises.3.6.1.4.1.2879.2.8.5.1.12.1.8.', '', $index); //# centos case
        echo "replace 'entreprises' ";
    }
    if ($k_array[0] == 'iso') {
        $ports_mapping['oid'] = str_replace('iso.3.6.1.4.1.2879.2.8.5.1.12.1.8.', '', $index); //# debian / docker case
        echo "replace 'iso' ";
    }
    if ($k_array[0] == '3') {
        $ports_mapping['oid'] = str_replace('3.6.1.4.1.2879.2.8.5.1.12.1.8.', '', $index); //# debian / docker case
        echo "replace '3' ";
    }
    if ($k_array[0] == 'SNMPv2-SMI::enterprises') {
        $ports_mapping['oid'] = str_replace('SNMPv2-SMI::enterprises.2879.2.8.5.1.12.1.8.', '', $index); //# debian / docker case
        echo "replace 'SNMPv2-SMI::enterprises' ";
    }

    $index = $ports_mapping['oid'];
    $server_name = SnmpQuery::get('.1.3.6.1.4.1.2879.2.8.5.1.12.1.16.' . $index)->value();
    $descr = 'Redundancy role: ' . $server_name;
    $sensor_value = (int) $entry;

    discover_sensor(
        null,
        'state',
        $device,
        '.1.3.6.1.4.1.2879.2.8.5.1.12.1.8.' . $index,
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
