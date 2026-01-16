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

    if (isset($data['portOpticalMonitoringInfoRxPower'])) {
        app('sensor-discovery')->discover(new \App\Models\Sensor([
            'poller_type' => 'snmp',
            'sensor_class' => 'dbm',
            'sensor_oid' => ".1.3.6.1.4.1.259.10.1.45.1.2.11.1.6.$ifIndex",
            'sensor_index' => "portOpticalMonitoringInfoRxPower.$ifIndex",
            'sensor_type' => 'edgecos',
            'sensor_descr' => "$ifName Transceiver Receive Power",
            'sensor_divisor' => 1,
            'sensor_multiplier' => 1,
            'sensor_limit_low' => isset($data['portTransceiverThresholdInfoRxPowerLowAlarm']) ? $data['portTransceiverThresholdInfoRxPowerLowAlarm'] / 100 : null,
            'sensor_limit_low_warn' => isset($data['portTransceiverThresholdInfoRxPowerLowWarn']) ? $data['portTransceiverThresholdInfoRxPowerLowWarn'] / 100 : null,
            'sensor_limit_warn' => isset($data['portTransceiverThresholdInfoRxPowerHighWarn']) ? $data['portTransceiverThresholdInfoRxPowerHighWarn'] / 100 : null,
            'sensor_limit' => isset($data['portTransceiverThresholdInfoRxPowerHighAlarm']) ? $data['portTransceiverThresholdInfoRxPowerHighAlarm'] / 100 : null,
            'sensor_current' => Number::cast($data['portOpticalMonitoringInfoRxPower'] ?? null),
            'entPhysicalIndex' => $ifIndex,
            'entPhysicalIndex_measured' => 'port',
            'group' => 'transceiver',
        ]));
    }

    if (isset($data['portOpticalMonitoringInfoTxPower'])) {
        app('sensor-discovery')->discover(new \App\Models\Sensor([
            'poller_type' => 'snmp',
            'sensor_class' => 'dbm',
            'sensor_oid' => ".1.3.6.1.4.1.259.10.1.45.1.2.11.1.5.$ifIndex",
            'sensor_index' => "portOpticalMonitoringInfoTxPower.$ifIndex",
            'sensor_type' => 'edgecos',
            'sensor_descr' => "$ifName Transceiver Transmit Power",
            'sensor_divisor' => 1,
            'sensor_multiplier' => 1,
            'sensor_limit_low' => isset($data['portTransceiverThresholdInfoTxPowerLowAlarm']) ? $data['portTransceiverThresholdInfoTxPowerLowAlarm'] / 100 : null,
            'sensor_limit_low_warn' => isset($data['portTransceiverThresholdInfoTxPowerLowWarn']) ? $data['portTransceiverThresholdInfoTxPowerLowWarn'] / 100 : null,
            'sensor_limit_warn' => isset($data['portTransceiverThresholdInfoTxPowerHighWarn']) ? $data['portTransceiverThresholdInfoTxPowerHighWarn'] / 100 : null,
            'sensor_limit' => isset($data['portTransceiverThresholdInfoTxPowerHighAlarm']) ? $data['portTransceiverThresholdInfoTxPowerHighAlarm'] / 100 : null,
            'sensor_current' => Number::cast($data['portOpticalMonitoringInfoTxPower'] ?? null),
            'entPhysicalIndex' => $ifIndex,
            'entPhysicalIndex_measured' => 'port',
            'group' => 'transceiver',
        ]));
    }
}
