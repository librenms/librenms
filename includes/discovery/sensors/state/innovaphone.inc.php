<?php
/**
 * LibreNMS sensors state discovery module for HP Procurve
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
 */
$oids = snmpwalk_cache_oid($device, 'voiceIfTable', [], 'INNO-MIB');

if (! empty($oids)) {
    //Create State Index
    $state_name = 'voiceIfState';
    $states = [
        ['value' => 0, 'generic' => 2, 'graph' => 0, 'descr' => 'down'],
        ['value' => 2, 'generic' => 0, 'graph' => 0, 'descr' => 'up'],
    ];
    create_state_index($state_name, $states);
    $num_oid = '.1.3.6.1.4.1.6666.2.1.1.1.4.';
    foreach ($oids as $index => $entry) {
        $ifname = hex2bin(str_replace(' ', '', $entry['voiceIfName']));
        if (empty($ifname)) {
            $ifname = $entry['voiceIfName'];
        }
        $name = 'Interface ' . $ifname;
        //Discover Sensors
        discover_sensor($valid['sensor'], 'state', $device, $num_oid . $index, $index, $state_name, $name, '1', '1', null, null, null, null, $entry['voiceIfState'], 'snmp', $index);

        //Create Sensor To State Index
        create_sensor_to_state_index($device, $state_name, $index);
    }
}
