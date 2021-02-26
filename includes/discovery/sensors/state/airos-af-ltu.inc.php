<?php
/**
 * airos-af-ltu.inc.php
 *
 * LibreNMS state discovery module for Ubiquiti airFiber 5XHD
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
 * @copyright  2020 Denny Friebe
 * @author     Denny Friebe <denny.friebe@icera-network.de>
 */
$oids = snmpwalk_cache_oid($device, 'afLTUStaTxRate', [], 'UBNT-AFLTU-MIB', null, '-OteQUsb'); //UBNT-AFLTU-MIB::afLTUStaTxRate
$oids = snmpwalk_cache_oid($device, 'afLTUStaRxRate', $oids, 'UBNT-AFLTU-MIB', null, '-OteQUsb'); //UBNT-AFLTU-MIB::afLTUStaRxRate

foreach ($oids as $index => $entry) {
    //Create State Index
    $txrate_state_name = 'afLTUStaTxRate';
    $rxrate_state_name = 'afLTUStaRxRate';

    $rate_states = [
        ['value' => 1, 'generic' => 2, 'graph' => 1, 'descr' => '1X (QPSK+SFBC)'],
        ['value' => 2, 'generic' => 2, 'graph' => 1, 'descr' => '2X (QPSK)'],
        ['value' => 4, 'generic' => 1, 'graph' => 1, 'descr' => '4X (16QAM)'],
        ['value' => 6, 'generic' => 1, 'graph' => 1, 'descr' => '6X (64QAM)'],
        ['value' => 8, 'generic' => 0, 'graph' => 1, 'descr' => '8X (256QAM)'],
        ['value' => 10, 'generic' => 0, 'graph' => 1, 'descr' => '10X (1024QAM)'],
        ['value' => 12, 'generic' => 0, 'graph' => 1, 'descr' => '12X (4096QAM)'],
    ];

    create_state_index($txrate_state_name, $rate_states);
    create_state_index($rxrate_state_name, $rate_states);

    //Discover Sensors
    discover_sensor($valid['sensor'], 'state', $device, '.1.3.6.1.4.1.41112.1.10.1.4.1.1.' . $index, 1, $txrate_state_name, 'TX Modulation Rate', '1', '1', null, null, null, null, $entry['afLTUStaTxRate']);
    discover_sensor($valid['sensor'], 'state', $device, '.1.3.6.1.4.1.41112.1.10.1.4.1.2.' . $index, 2, $rxrate_state_name, 'RX Modulation Rate', '1', '1', null, null, null, null, $entry['afLTUStaRxRate']);

    //Create Sensor To State Index
    create_sensor_to_state_index($device, $txrate_state_name, 1);
    create_sensor_to_state_index($device, $rxrate_state_name, 2);

    break;
}

unset(
    $oids,
    $index,
    $entry,
    $rate_states,
    $txrate_state_name,
    $rxrate_state_name
);
