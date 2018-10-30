<?php
/**
 * etherprobe2.inc.php
 *
 * LibreNMS state discover module for Atal EtherProbe2
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

$status_value = snmp_get($device, 'spStatus.0', '-Oqve', 'SPAGENT-MIB');

if (is_numeric($status_value)) {
    $state = 'spStatus';
    $state_index_id = create_state_index($state);
    if ($state_index_id) {
        $states = array(
            array($state_index_id, 'noStatus', 1, 1, 1),
            array($state_index_id, 'normal', 1, 2, 0),
            array($state_index_id, 'warning', 1, 3, 1),
            array($state_index_id, 'critical', 1, 4, 2),
            array($state_index_id, 'sensorError', 1, 5, 2),
        );
    }

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
    discover_sensor($valid['sensor'], 'state', $device, '.1.3.6.1.4.1.3854.1.1.2.0', 'ethernetprobe2', $state, 'Sensor state', '1', '1', null, null, null, null, $status_value);
    create_sensor_to_state_index($device, $state, 'ethernetprobe2');
}
