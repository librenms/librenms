<?php
/**
 * fabos.inc.php
 *
 * LibreNMS states discovery module for fabos
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
 * @copyright  2017 Neil Lathwood
 * @author     Neil Lathwood <neil@lathwood.co.uk>
 */

foreach ($pre_cache['fabos_sensors'] as $data) {
    if (is_numeric($data['swSensorValue']) && $data['swSensorValue'] !== '-2147483648') {
        $descr = $data['swSensorInfo'];
        $state_index_id = create_state_index($descr);
        if ($state_index_id !== null) {
            $states = array(
                array($state_index_id, 'unknown', 0, 1, 3),
                array($state_index_id, 'faulty', 1, 2, 2),
                array($state_index_id, 'below-min', 1, 3, 1),
                array($state_index_id, 'nominal', 1, 4, 0),
                array($state_index_id, 'above-max', 1, 5, 1),
                array($state_index_id, 'absent', 1, 6, 1),
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
        $index = $data['swSensorIndex'];
        $oid = '.1.3.6.1.4.1.1588.2.1.1.1.1.22.1.3.' . $index;
        $value = $data['swSensorStatus'];
        discover_sensor($valid['sensor'], 'state', $device, $oid, $index, 'fabos_state', $descr, '1', '1', null, null, null, null, $value);
        create_sensor_to_state_index($device, 'fabos_state', 1);
    }
}
