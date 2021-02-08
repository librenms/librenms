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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 * @copyright  2017 Neil Lathwood
 * @author     Neil Lathwood <gh+n@laf.io>
 */
foreach ($pre_cache['procurve_hpicfSensorTable'] as $index => $data) {
    $state_name = $data['hpicfSensorObjectId'];
    $state_oid = '.1.3.6.1.4.1.11.2.14.11.1.2.6.1.4.';
    $state_descr = $data['hpicfSensorDescr'];
    $state = $data['hpicfSensorStatus'];
    $state_index = $state_name . '.' . $index;

    $states = [
        ['value' => 1, 'generic' => 3, 'graph' => 0, 'descr' => 'unknown'],
        ['value' => 2, 'generic' => 2, 'graph' => 1, 'descr' => 'bad'],
        ['value' => 3, 'generic' => 1, 'graph' => 1, 'descr' => 'warning'],
        ['value' => 4, 'generic' => 0, 'graph' => 1, 'descr' => 'good'],
        ['value' => 5, 'generic' => 3, 'graph' => 0, 'descr' => 'notPresent'],
    ];
    create_state_index($state_name, $states);

    discover_sensor($valid['sensor'], 'state', $device, $state_oid . $index, $state_index, $state_name, $state_descr, '1', '1', null, null, null, null, $state);
    create_sensor_to_state_index($device, $state_name, $state_index);
}
