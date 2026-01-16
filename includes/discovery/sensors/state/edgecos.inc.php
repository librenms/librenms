<?php

/**
 * edgecos.inc.php
 *
 * -Description-
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
 *
 * @copyright  2026 Frederik Kriewitz
 * @author     Frederik Kriewitz <frederik@kriewitz.eu>
 */
$os->getSnmpQuery()->walk('switchFanTable')->mapTable(function ($data, $index, $subIndex): void {
    //Create fan state Index
    $state_name = 'edgecos-switchFanStatus';
    $states = [
        ['value' => 1, 'generic' => 0, 'graph' => 1, 'descr' => 'ok'],
        ['value' => 2, 'generic' => 2, 'graph' => 0, 'descr' => 'failure'],
    ];
    create_state_index($state_name, states: $states);

    // create fan state sensor
    app('sensor-discovery')->discover(new \App\Models\Sensor([
        'poller_type' => 'snmp',
        'sensor_class' => 'state',
        'sensor_oid' => ".1.3.6.1.4.1.259.10.1.45.1.1.9.1.3.$index.$subIndex",
        'sensor_index' => "$state_name.$index.$subIndex",
        'sensor_type' => $state_name,
        'sensor_descr' => "Fan $index.$subIndex status",
        'sensor_current' => $data['switchFanStatus'],
    ]));
});

$os->getSnmpQuery()->walk('switchInfoTable')->mapTable(function ($data, $index): void {
    //Create power state Index
    $state_name = 'edgecos-swPowerStatus';
    $states = [
        ['value' => 1, 'generic' => 0, 'graph' => 1, 'descr' => 'internalPower'],
        ['value' => 2, 'generic' => 0, 'graph' => 2, 'descr' => 'redundantPower'],
        ['value' => 3, 'generic' => 0, 'graph' => 3, 'descr' => 'internalAndRedundantPower'],
    ];
    create_state_index($state_name, states: $states);

    // create power state sensor
    app('sensor-discovery')->discover(new \App\Models\Sensor([
        'poller_type' => 'snmp',
        'sensor_class' => 'state',
        'sensor_oid' => ".1.3.6.1.4.1.259.10.1.45.1.1.3.1.8.$index",
        'sensor_index' => "$state_name.$index",
        'sensor_type' => $state_name,
        'sensor_descr' => "Power $index status",
        'sensor_current' => $data['swPowerStatus'],
    ]));
});
