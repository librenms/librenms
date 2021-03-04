<?php
/**
 * aruba-instant.inc.php
 *
 * LibreNMS state discovery module for Aruba Instant
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
 * @copyright  2019 Timothy Willey
 * @author     Timothy Willey <developer@timothywilley.net>
 */
$ai_mib = 'AI-AP-MIB';
$oids = snmpwalk_group($device, 'aiAPSerialNum', $ai_mib);
$oids = snmpwalk_group($device, 'aiAPStatus', $ai_mib, 1, $oids);
$oids = snmpwalk_group($device, 'aiRadioStatus', $ai_mib, 1, $oids);
$oids = snmpwalk_group($device, 'aiAPName', $ai_mib, 1, $oids);

if (! empty($oids)) {
    $ap_state_name = 'aiAPStatus';
    //Create State Translation
    $ap_states = [
        ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'up'],
        ['value' => 2, 'generic' => 2, 'graph' => 0, 'descr' => 'down'],
    ];

    //Create State Index
    $radio_state_name = 'aiRadioStatus';
    //Create State Translation
    $radio_states = [
        ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'up'],
        ['value' => 2, 'generic' => 3, 'graph' => 0, 'descr' => 'down'],
    ];

    create_state_index($ap_state_name, $ap_states);
    create_state_index($radio_state_name, $radio_states);

    foreach ($oids as $ap_index => $ap_entry) {
        $ap_state_index = implode('.', array_map('hexdec', explode(':', $ap_index)));
        $ap_state_oid = '.1.3.6.1.4.1.14823.2.3.3.1.2.1.1.11.' . $ap_state_index;
        $ap_descr = $ap_entry['aiAPName'] . ' (' . $ap_entry['aiAPSerialNum'] . ')';

        discover_sensor($valid['sensor'], 'state', $device, $ap_state_oid, $ap_state_index, $ap_state_name, $ap_descr, '1', '1', null, null, null, null, $ap_entry['aiAPStatus'], 'snmp', null, null, null, 'Cluster APs');

        //Create Sensor To State Index
        create_sensor_to_state_index($device, $ap_state_name, $ap_state_index);

        foreach ($ap_entry['aiRadioStatus'] as $radio_index => $radio_status) {
            $radio_state_index = implode('.', [$ap_state_index, $radio_index]);
            $radio_state_oid = '.1.3.6.1.4.1.14823.2.3.3.1.2.2.1.20.' . $radio_state_index;

            discover_sensor($valid['sensor'], 'state', $device, $radio_state_oid, $radio_state_index, $radio_state_name, $ap_descr . ' Radio ' . $radio_index, '1', '1', null, null, null, null, $radio_status, 'snmp', null, null, null, 'Cluster Radios');

            //Create Sensor To State Index
            create_sensor_to_state_index($device, $radio_state_name, $radio_state_index);
        }
    } //end foreach
} //end if
