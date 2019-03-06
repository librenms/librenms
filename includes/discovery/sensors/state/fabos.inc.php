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
        $states = [
            ['value' => 1, 'generic' => 3, 'graph' => 0, 'descr' => 'unknown'],
            ['value' => 2, 'generic' => 2, 'graph' => 1, 'descr' => 'faulty'],
            ['value' => 3, 'generic' => 1, 'graph' => 1, 'descr' => 'below-min'],
            ['value' => 4, 'generic' => 0, 'graph' => 1, 'descr' => 'nominal'],
            ['value' => 5, 'generic' => 1, 'graph' => 1, 'descr' => 'above-max'],
            ['value' => 6, 'generic' => 1, 'graph' => 1, 'descr' => 'absent'],
        ];
        create_state_index($state_name, $states);

        $index = $data['swSensorIndex'];
        $oid = '.1.3.6.1.4.1.1588.2.1.1.1.1.22.1.3.' . $index;
        $value = $data['swSensorStatus'];
        discover_sensor($valid['sensor'], 'state', $device, $oid, $index, $descr, $descr, 1, 1, null, null, null, null, $value);
        create_sensor_to_state_index($device, $descr, $index);
    }
}
