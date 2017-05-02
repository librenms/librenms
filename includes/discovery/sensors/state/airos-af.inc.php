<?php
/**
 * airos-af.inc.php
 *
 * Ubiquiti AirFiber States
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
 * @copyright  2017 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

$mod = snmp_get($device, 'curTXModRate.1', "-Ovqe", "UBNT-AirFIBER-MIB");

if (is_numeric($mod)) {
    $state_name = 'curTXModRate';
    $index      = $state_name;
    $state_index_id = create_state_index($state_name);

    if ($state_index_id !== null) {
        $states = array(
            array($state_index_id, 'qPSK-SISO-1-4x', 1, 0, 1),
            array($state_index_id, 'qPSK-SISO-1x', 1, 1, 1),
            array($state_index_id, 'qPSK-MIMO-2x', 1, 2, 0),
            array($state_index_id, 'qAM16-MIMO-4x', 1, 4, 0),
            array($state_index_id, 'qAM64-MIMO-6x', 1, 6, 0),
            array($state_index_id, 'qAM256-MIMO-8x', 1, 8, 0),
        );

        foreach ($states as $value) {
            $insert = array(
                'state_index_id' => $value[0],
                'state_descr' => $value[1],
                'state_draw_graph' => $value[2],
                'state_value' => $value[3],
                'state_generic_value' => $value[4]
            );
            dbInsert($insert, 'state_translations');
        }
    }

    discover_sensor(
        $valid['sensor'],
        'state',
        $device,
        '.1.3.6.1.4.1.41112.1.3.2.1.2.1',
        $index,
        $state_name,
        'Tx Modulation Rate',
        1,
        1,
        null,
        null,
        null,
        null,
        $mod
    );
    create_sensor_to_state_index($device, $state_name, $index);
}

$gps = snmp_get($device, 'gpsSync.1', "-Ovqe", "UBNT-AirFIBER-MIB");

if (is_numeric($gps)) {
    $state_name = 'gpsSync';
    $index      = $state_name;
    $state_index_id = create_state_index($state_name);

    if ($state_index_id !== null) {
        $states = array(
            array($state_index_id, 'off', 1, 1, 1),
            array($state_index_id, 'on', 1, 4, 0),
        );

        foreach ($states as $value) {
            $insert = array(
                'state_index_id' => $value[0],
                'state_descr' => $value[1],
                'state_draw_graph' => $value[2],
                'state_value' => $value[3],
                'state_generic_value' => $value[4]
            );
            dbInsert($insert, 'state_translations');
        }
    }

    discover_sensor(
        $valid['sensor'],
        'state',
        $device,
        '.1.3.6.1.4.1.41112.1.3.1.1.8.1',
        $index,
        $state_name,
        'GPS Sync',
        1,
        1,
        null,
        null,
        null,
        null,
        $mod
    );
    create_sensor_to_state_index($device, $state_name, $index);
}

unset($mod, $gps, $state_name, $index, $state_index_id, $states, $descr);
