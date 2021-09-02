<?php
/**
 * serverscheck.inc.php
 *
 * LibreNMS state discover module for serverscheck flooding sensor
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
 * @copyright  2018 Marcus Pink
 * @author     Marcus Pink <mpink@avantgarde-labs.de>
 */

use Illuminate\Support\Str;

$serverscheck_oids = [
    'sensor1Value.0' => '.1.3.6.1.4.1.17095.3.2.0',
    'sensor2Value.0' => '.1.3.6.1.4.1.17095.3.6.0',
    'sensor3Value.0' => '.1.3.6.1.4.1.17095.3.10.0',
    'sensor4Value.0' => '.1.3.6.1.4.1.17095.3.14.0',
    'sensor5Value.0' => '.1.3.6.1.4.1.17095.3.18.0',
];

foreach ($pre_cache['serverscheck_control'] as $oid_name => $oid_value) {
    if ((Str::contains($oid_name, 'name')) && (Str::contains($oid_value, ['Flooding', 'Leckage']))) {
        preg_match("/(\d+)/", $oid_name, $temp_x);
        $tmp_oid = 'sensor' . $temp_x[0] . 'Value.0';
        $current = $pre_cache['serverscheck_control'][$tmp_oid];
        $state_name = 'Serverscheck_FloodSensor';
        if ($current) {
            $index = str_replace('.0', '', $oid_name);
            $descr = $oid_value;
            $states = [
                ['value' => 1, 'generic' => 1, 'graph' => 1, 'descr' => '-'],
                ['value' => 2, 'generic' => 0, 'graph' => 1, 'descr' => 'DRY'],
                ['value' => 4, 'generic' => 2, 'graph' => 1, 'descr' => 'WET'],
            ];
            create_state_index($state_name, $states);

            discover_sensor($valid['sensor'], 'state', $device, $serverscheck_oids[$tmp_oid], $index, $state_name, $descr, 1, 1, null, null, null, null, 1);
            create_sensor_to_state_index($device, $state_name, $index);
        }
    }
}
