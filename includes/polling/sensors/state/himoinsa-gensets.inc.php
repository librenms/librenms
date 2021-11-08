<?php
/**
 * himoinsa-gensets.inc.php
 *
 * LibreNMS state sensor poller module for Himoinsa Gensets
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
 * @copyright  2021 Daniel Baeza
 * @author     TheGreatDoc <doctoruve@gmail.com>
 */
if ($device['os'] == 'himoinsa-gensets') {
    d_echo('Polling Himoinsa Gensets State Sensor \n');
    if (in_array($sensor['sensor_type'], ['statusCommAlarm', 'statusMotor', 'statusMode', 'statusBT', 'statusAL', 'statusComm'])) {
        switch ($sensor['sensor_type']) {
            case 'statusBT':
                // Check if CEA7/CEM7 info is available and retrieve data
                $status = SnmpQuery::get('HIMOINSAv14-MIB::status.0')->value();
                $statusTransferPump = ($status & 64) ?? 0;
                d_echo('Transfer Pump: ' . $statusTransferPump);
                $sensor_value = $statusTransferPump;
                break;
            case 'statusAL':
                // Check if CEA7/CEM7 info is available and retrieve data
                $status = SnmpQuery::get('HIMOINSAv14-MIB::status.0')->value();
                $statusAlarm = ($status & 128) ?? 0;
                d_echo('Alarm: ' . $statusAlarm);
                $sensor_value = $statusAlarm;
                break;
            case 'statusMode':
                // Check if CEA7/CEM7 info is available and retrieve data
                $status = SnmpQuery::get('HIMOINSAv14-MIB::status.0')->value();
                $statusMode =
                    ($status & 4) |
                    ($status & 8) |
                    ($status & 16) |
                    ($status & 32);
                d_echo('Mode: ' . $statusMode);
                $sensor_value = $statusMode;
                break;
            case 'statusComm':
                // Check if CEA7/CEM7 and CEC7 info is available and retrieve data
                $status = SnmpQuery::get(['HIMOINSAv14-MIB::status.0', 'HIMOINSAv14-MIB::statusConm.0'])->values();
                if (isset($status['HIMOINSAv14-MIB::statusConm[0]']) && ($status['HIMOINSAv14-MIB::statusConm[0]'] != 0)) {
                    $statusComm =
                        ($status['HIMOINSAv14-MIB::statusConm[0]'] & 32) |
                        ($status['HIMOINSAv14-MIB::statusConm[0]'] & 64);
                } else {
                    $statusComm = ($status['HIMOINSAv14-MIB::status.0'] & 512) | ($status['HIMOINSAv14-MIB::status.0'] & 256);
                }
                d_echo('Switch: ' . $statusComm);
                $sensor_value = $statusComm;
                break;
            case 'statusCommAlarm':
                // Check if CEC7 info is available and retrieve data
                $statusConm = SnmpQuery::get('HIMOINSAv14-MIB::statusConm.0')->value();
                $statusCommAlarm = ($statusConm & 1) ?? 0;
                d_echo('SWAlarm: ' . $statusCommAlarm);
                $sensor_value = $statusCommAlarm;
                break;
            case 'statusMotor':
                // Check if CEA7/CEM7 info is available and retrieve data
                $status = SnmpQuery::get('HIMOINSAv14-MIB::status.0')->value();
                $statusMotor = ($status & 1) | ($status & 2);
                d_echo('Engine: ' . $statusMotor);
                $sensor_value = $statusMotor;
                break;
        }
    }
}
