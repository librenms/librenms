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
    // create fan speed sensor
    app('sensor-discovery')->discover(new \App\Models\Sensor([
        'poller_type' => 'snmp',
        'sensor_class' => 'fanspeed',
        'sensor_oid' => ".1.3.6.1.4.1.259.10.1.45.1.1.9.1.6.$index.$subIndex",
        'sensor_index' => "edgecos-switchFanOperSpeed.$index.$subIndex",
        'sensor_type' => 'edgecos',
        'sensor_descr' => "Fan $index.$subIndex speed",
        'sensor_current' => $data['switchFanOperSpeed'],
    ]));
});
