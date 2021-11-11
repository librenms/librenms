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
use LibreNMS\OS\HimoinsaGensets;

if ($device['os'] == 'himoinsa-gensets') {
    d_echo('Polling Himoinsa Gensets State Sensor \n');
    if (in_array($sensor['sensor_type'], ['statusCommAlarm', 'statusMotor', 'statusMode', 'statusTransferPump', 'statusAlarm', 'statusComm'])) {
        switch ($sensor['sensor_type']) {
            case 'statusTransferPump':
                // Check if CEA7/CEM7 info is available and retrieve data
                $status = SnmpQuery::get('HIMOINSAv14-MIB::status.0')->value();
                $statusTransferPump = HimoinsaGensets::dectostate('status.0', $status['HIMOINSAv14-MIB::status.0'], 'TransferPump');
                d_echo('Transfer Pump: ' . $statusTransferPump);
                $sensor_value = $statusTransferPump;
                break;
            case 'statusAlarm':
                // Check if CEA7/CEM7 info is available and retrieve data
                $status = SnmpQuery::get('HIMOINSAv14-MIB::status.0')->value();
                $statusAlarm = HimoinsaGensets::dectostate('status.0', $status['HIMOINSAv14-MIB::status.0'], 'Alarm');
                d_echo('Alarm: ' . $statusAlarm);
                $sensor_value = $statusAlarm;
                break;
            case 'statusMode':
                // Check if CEA7/CEM7 info is available and retrieve data
                $status = SnmpQuery::get('HIMOINSAv14-MIB::status.0')->value();
                $statusMode = HimoinsaGensets::dectostate('status.0', $status['HIMOINSAv14-MIB::status.0'], 'Mode');
                d_echo('Mode: ' . $statusMode);
                $sensor_value = $statusMode;
                break;
            case 'statusComm':
                // Check if CEA7/CEM7 and CEC7 info is available and retrieve data
                $status = SnmpQuery::get(['HIMOINSAv14-MIB::status.0', 'HIMOINSAv14-MIB::statusConm.0'])->values();
                if (isset($status['HIMOINSAv14-MIB::statusConm[0]']) && ($status['HIMOINSAv14-MIB::statusConm[0]'] != 0)) {
                    $statusComm = HimoinsaGensets::dectostate('statusConm.0', $status['HIMOINSAv14-MIB::statusConm[0]'], 'Comm');
                } else {
                    $statusComm = HimoinsaGensets::dectostate('status.0', $status['HIMOINSAv14-MIB::status.0'], 'Comm');
                }
                d_echo('Switch: ' . $statusComm);
                $sensor_value = $statusComm;
                break;
            case 'statusCommAlarm':
                // Check if CEC7 info is available and retrieve data
                $statusConm = SnmpQuery::get('HIMOINSAv14-MIB::statusConm.0')->value();
                $statusCommAlarm = HimoinsaGensets::dectostate('statusConm.0', $status['HIMOINSAv14-MIB::statusConm[0]'], 'CommAlarm');
                d_echo('SWAlarm: ' . $statusCommAlarm);
                $sensor_value = $statusCommAlarm;
                break;
            case 'statusMotor':
                // Check if CEA7/CEM7 info is available and retrieve data
                $status = SnmpQuery::get('HIMOINSAv14-MIB::status.0')->value();
                $statusMotor = HimoinsaGensets::dectostate('status.0', $status['HIMOINSAv14-MIB::status.0'], 'Motor');
                d_echo('Engine: ' . $statusMotor);
                $sensor_value = $statusMotor;
                break;
        }
    }
}
