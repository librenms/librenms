<?php
/**
 * tpdin.inc.php
 *
 * LibreNMS voltage discovery module for Tycon Systems TPDIN
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
$tpdin_oids = [
    [
        'oid'     => '.1.3.6.1.4.1.45621.2.2.5.0',
        'index'   => 'voltage1',
        'descr'   => 'Voltage 1',
        'current' => $pre_cache['tpdin_monitor'][0]['voltage1'],
    ],
    [
        'oid'     => '.1.3.6.1.4.1.45621.2.2.6.0',
        'index'   => 'voltage2',
        'descr'   => 'Voltage 2',
        'current' => $pre_cache['tpdin_monitor'][0]['voltage2'],
    ],
    [
        'oid'     => '.1.3.6.1.4.1.45621.2.2.7.0',
        'index'   => 'voltage3',
        'descr'   => 'Voltage 3',
        'current' => $pre_cache['tpdin_monitor'][0]['voltage3'],
    ],
    [
        'oid'     => '.1.3.6.1.4.1.45621.2.2.8.0',
        'index'   => 'voltage4',
        'descr'   => 'Voltage 4',
        'current' => $pre_cache['tpdin_monitor'][0]['voltage4'],
    ],
];

foreach ($tpdin_oids as $data) {
    if ($data['current'] > 0) {
        discover_sensor($valid['sensor'], 'voltage', $device, $data['oid'], $data['index'], $device['os'], $data['descr'], 10, '1', null, null, null, null, $data['current']);
    }
}

unset($tpdin_oids);
