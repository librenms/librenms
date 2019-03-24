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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2019 Timothy Willey
 * @author     Timothy Willey <developer@timothywilley.net>
 */
$ai_mib = 'AI-AP-MIB';
$oids = snmpwalk_group($device, 'aiStateGroup', $ai_mib);
if (!empty($oids)) {
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
        ['value' => 2, 'generic' => 1, 'graph' => 0, 'descr' => 'down'],
    ];

    create_state_index($ap_state_name, $ap_states);
    create_state_index($radio_state_name, $radio_states);

    foreach ($oids as $ap_index => $ap_entry) {
        // $ap_state_index = '';
        // $macparts = explode(':', $ap_index);
        // foreach ($macparts as $part) {
        //     $ap_state_index .= hexdec($part).'.';
        // }
        $ap_state_index = implode('.', array_map('hexdec', explode(':', $ap_index)));

        d_echo('ap_state_index  pre-trim: '.$ap_state_index.PHP_EOL);
        // $combined_oid = rtrim(sprintf('%s::%s.%s', $ai_mib, 'aiAPStatus', $ap_state_index), '.');
        $combined_oid = implode('.', [$ai_mib.'::'.'aiAPStatus', $ap_state_index]);
        $ap_state_oid = snmp_translate($combined_oid, 'ALL', 'arubaos', '-On', null);
        // $ap_state_index = rtrim($ap_state_index, '.');
        d_echo('ap_state_index post-trim: '.$ap_state_index.PHP_EOL);
        d_echo('combined_oid:             '.$combined_oid.PHP_EOL);
        d_echo('ap_state_oid:             '.$ap_state_oid.PHP_EOL);
        d_echo('value:                    '.$ap_entry['aiAPSerialNum'].PHP_EOL);

            // $combined_oid = sprintf('%s::%s.%s', 'AI-AP-MIB', 'aiAPTotalMemory', $oid_index);

            // $usage_oid = snmp_translate($combined_oid, 'ALL', 'arubaos', '-On', null);

        discover_sensor($valid['sensor'], 'state', $device, $ap_state_oid, $ap_state_index, $ap_state_name, $ap_entry['aiAPSerialNum'], '1', '1', null, null, null, null, $ap_entry[$ap_state_name], 'snmp', null, null, null, 'Cluster APs');

        //Create Sensor To State Index
        create_sensor_to_state_index($device, $ap_state_name, $ap_state_index);

        foreach ($ap_entry['aiRadioStatus'] as $radio_index => $radio_status) {
            // $radio_state_index = '';
            // $macparts = explode(':', $ap_index);
            // foreach ($macparts as $part) {
            //     $radio_state_index .= hexdec($part).'.';
            // }
            $radio_state_index = implode('.', array_map('hexdec', explode(':', $ap_index)));
            // $combined_oid = rtrim(sprintf('%s::%s.%s%s', $ai_mib, 'aiRadioStatus', $radio_state_index, $radio_index), '.');
            $combined_oid = implode('.', [$ai_mib.'::'.'aiAPStatus', $radio_state_index, $radio_index]);
            $radio_state_oid = snmp_translate($combined_oid, 'ALL', 'arubaos', '-On', null);
            // $radio_state_index = rtrim($radio_state_index.$radio_index, '.');

            d_echo('radio_state_index post-trim: '.$radio_state_index.PHP_EOL);
            d_echo('combined_oid:                '.$combined_oid.PHP_EOL);
            d_echo('radio_state_oid:             '.$radio_state_oid.PHP_EOL);
            d_echo('value:                       '.$radio_status.PHP_EOL);

            discover_sensor($valid['sensor'], 'state', $device, $radio_state_oid, $radio_state_index, $radio_state_name, $ap_entry['aiAPSerialNum'].' Radio '.$radio_index, '1', '1', null, null, null, null, $radio_status, 'snmp', null, null, null, 'Cluster Radios');

            //Create Sensor To State Index
            create_sensor_to_state_index($device, $radio_state_name, $radio_state_index);
        }
    } //end foreach
} //end if
