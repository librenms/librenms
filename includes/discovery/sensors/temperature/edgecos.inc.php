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

use LibreNMS\Util\Number;

foreach ($os->getTransceiverDDMData() as $data) {
    $ifIndex = $data['ifIndex'];
    $ifName = $data['ifName'];

    if (isset($data['portOpticalMonitoringInfoTemperature'])) {
        app('sensor-discovery')->discover(new \App\Models\Sensor([
            'poller_type' => 'snmp',
            'sensor_class' => 'temperature',
            'sensor_oid' => ".1.3.6.1.4.1.259.10.1.45.1.2.11.1.2.$ifIndex",
            'sensor_index' => "portOpticalMonitoringInfoTemperature.$ifIndex",
            'sensor_type' => 'edgecos',
            'sensor_descr' => "$ifName Transceiver Temperature",
            'sensor_divisor' => 1,
            'sensor_multiplier' => 1,
            'sensor_limit_low' => isset($data['portTransceiverThresholdInfoTemperatureLowAlarm']) ? $data['portTransceiverThresholdInfoTemperatureLowAlarm'] / 100 : null,
            'sensor_limit_low_warn' => isset($data['portTransceiverThresholdInfoTemperatureLowWarn']) ? $data['portTransceiverThresholdInfoTemperatureLowWarn'] / 100 : null,
            'sensor_limit_warn' => isset($data['portTransceiverThresholdInfoTemperatureHighWarn']) ? $data['portTransceiverThresholdInfoTemperatureHighWarn'] / 100 : null,
            'sensor_limit' => isset($data['portTransceiverThresholdInfoTemperatureHighAlarm']) ? $data['portTransceiverThresholdInfoTemperatureHighAlarm'] / 100 : null,
            'sensor_current' => Number::cast($data['portOpticalMonitoringInfoTemperature'] ?? null),
            'entPhysicalIndex' => $ifIndex,
            'entPhysicalIndex_measured' => 'port',
            'group' => 'transceiver',
        ]));
    }
}

$os->getSnmpQuery()->walk('switchThermalTempTable')->mapTable(function ($data, $index, $subIndex): void {
    // create switch temperature sensors
    app('sensor-discovery')->discover(new \App\Models\Sensor([
        'poller_type' => 'snmp',
        'sensor_class' => 'temperature',
        'sensor_oid' => ".1.3.6.1.4.1.259.10.1.45.1.1.11.1.3.$index.$subIndex",
        'sensor_index' => "edgecos-switchThermalTempValue.$index.$subIndex",
        'sensor_type' => 'edgecos',
        'sensor_descr' => "Switch Temperature $index.$subIndex",
        'sensor_limit_low' => 0,
        'sensor_limit_warn' => 85,
        'sensor_limit' => 90,
        'sensor_current' => $data['switchThermalTempValue'],
    ]));
});
