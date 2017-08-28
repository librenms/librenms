<?php
/**
 * procurve.inc.php
 *
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2017 Neil Lathwood
 * @author     Neil Lathwood <gh+n@laf.io>
 */

foreach ($pre_cache['procurve_hpicfSensorTable'] as $index => $data) {
    $status_name    = $data['hpicfSensorObjectId'];
    $status_oid     = '.1.3.6.1.4.1.11.2.14.11.1.2.6.1.4.';
    $status_descr   = $data['hpicfSensorDescr'];
    $state          = $data['hpicfSensorStatus'];
    $tmp_index      = $status_name . '.' . $index;
    $state_index_id = create_state_index($status_name);
    if ($state_index_id !== null) {
        $states = array(
            array($state_index_id, 'unknown', 0, 1, 3),
            array($state_index_id, 'bad', 1, 2, 2),
            array($state_index_id, 'warning', 1, 3, 1),
            array($state_index_id, 'good', 1, 4, 0),
            array($state_index_id, 'notPresent', 0, 5, 3),
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
    discover_sensor($valid['sensor'], 'state', $device, $status_oid . $index, $tmp_index, $status_name, $status_descr, '1', '1', null, null, null, null, $state);
    create_sensor_to_state_index($device, $status_name, $tmp_index);
}
