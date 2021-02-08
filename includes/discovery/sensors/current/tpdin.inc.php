<?php
/**
 * tpdin.inc.php
 *
 * LibreNMS current discovery module for Tycon Systems TPDIN
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
        'oid'     => '.1.3.6.1.4.1.45621.2.2.9.0',
        'index'   => 'current1',
        'descr'   => 'Current 1',
        'current' => $pre_cache['tpdin_monitor'][0]['current1'],
    ],
    [
        'oid'     => '.1.3.6.1.4.1.45621.2.2.10.0',
        'index'   => 'current2',
        'descr'   => 'Current 2',
        'current' => $pre_cache['tpdin_monitor'][0]['current2'],
    ],
    [
        'oid'     => '.1.3.6.1.4.1.45621.2.2.11.0',
        'index'   => 'current3',
        'descr'   => 'Current 3',
        'current' => $pre_cache['tpdin_monitor'][0]['current3'],
    ],
    [
        'oid'     => '.1.3.6.1.4.1.45621.2.2.12.0',
        'index'   => 'current4',
        'descr'   => 'Current 4',
        'current' => $pre_cache['tpdin_monitor'][0]['current4'],
    ],
];

foreach ($tpdin_oids as $data) {
    if ($data['current'] > 0) {
        discover_sensor($valid['sensor'], 'current', $device, $data['oid'], $data['index'], $device['os'], $data['descr'], 10, '1', null, null, null, null, $data['current']);
    }
}

unset($tpdin_oids);
