<?php
/**
 * himoinsa-gensets.inc.php
 *
 * LibreNMS state sensor discovery module for Himoinsa Gensets
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
 * @copyright  2021 Daniel Baeza & Tony Murray
 * @author     TheGreatDoc <doctoruve@gmail.com> & murrant <murraytony@gmail.com>
 */

/*
CEC7 (a.k.a CEA7CC2) bitmap status ( R G B T Mn Au Al)
statusConm.0
R = Mains commutator closed
G = Gen commutator closed
B = Blocked mode
T = Test mode
Mn = Manual mode
Au = Auto mode
Al = Active commutator alarm

In LibreNMS it equals to 3 state sensors:
Closed commutator: Mains or Genset (what commutator is closed, so where the power comes from)
Genset Mode: Block, Test, Manual, Auto (the 4 modes of the genset)
Alarm: Yes or No (If there is a commutator alarm)

Example:
Value = 66
Value binary = 1000010
States equals to:
- Alarm: No active alarm
- Genset Mode: Auto
- Closed commutator: Mains

CEA7 / CEM7 (CEA7 is a combination of CEC7 + CEM7 in a single Central) bitmap status (R G Al Bt B T Mn Au P A)
status.0
R = Mains commutator closed
G = Gen commutator closed
Al = Active Alarm
Bt = Transfer Pump
B = Blocked mode
T = Test mode
Mn = Manual mode
Au = Auto mode
P = Motor Stopped
A = Motor Running
*/
$status = SnmpQuery::get(['HIMOINSAv14-MIB::status.0', 'HIMOINSAv14-MIB::statusConm.0'])->values();

if (isset($status['HIMOINSAv14-MIB::status.0'])) {
    $statusMotor = ($status['HIMOINSAv14-MIB::status.0'] & 1) | ($status['HIMOINSAv14-MIB::status.0'] & 2);
    $statusMode =
        ($status['HIMOINSAv14-MIB::status.0'] & 4) |
        ($status['HIMOINSAv14-MIB::status.0'] & 8) |
        ($status['HIMOINSAv14-MIB::status.0'] & 16) |
        ($status['HIMOINSAv14-MIB::status.0'] & 32);
    $statusAlarm = ($status['HIMOINSAv14-MIB::status.0'] & 128) ?? 0;
    $statusTransferPump = ($status['HIMOINSAv14-MIB::status.0'] & 64) ?? 0;
    if (isset($status['HIMOINSAv14-MIB::statusConm[0]']) && ($status['HIMOINSAv14-MIB::statusConm[0]'] != 0)) {
        $statusComm =
            ($status['HIMOINSAv14-MIB::statusConm[0]'] & 32) |
            ($status['HIMOINSAv14-MIB::statusConm[0]'] & 64);
        $commgroup = 'CEC7';
        $statusCommAlarm = ($status['HIMOINSAv14-MIB::statusConm[0]'] & 1) ?? 0;
    } else {
        $statusComm = ($status['HIMOINSAv14-MIB::status.0'] & 512) | ($status['HIMOINSAv14-MIB::status.0'] & 256);
        $commgroup = 'CEA7/CEM7';
    }
    
    d_echo('Motor ' . $statusMotor);
    d_echo('Mode ' . $statusMode);
    d_echo('TPump ' . $statusTransferPump);
    d_echo('Comm ' . $statusComm);
    d_echo('Alarm ' . $statusAlarm);
    d_echo('CommAlarm ' . $statusCommAlarm);
    d_echo('Comm Group ' . $commgroup);

    // Check for CEC7 and get CEC7 alarm state.
    if ($commgroup === 'CEC7') {
        // CEC7 Commutator Alarm
        $state_name = 'statusCommAlarm';
        $states = [
            ['value' => 0, 'generic' => 0, 'graph' => 0, 'descr' => 'No Alarm'],
            ['value' => 1, 'generic' => 2, 'graph' => 0, 'descr' => 'Alarm'],
        ];
        create_state_index($state_name, $states);

        $descr = 'Alarm';
        $sensor_index = 0;
        //Discover Sensors
        discover_sensor(
            $valid['sensor'],
            'state',
            $device,
            null,
            $sensor_index,
            $state_name,
            $descr,
            1,
            1,
            null,
            null,
            null,
            null,
            $statusCommAlarm,
            'snmp',
            null,
            null,
            null,
            'CEC7'
        );

        //Create Sensor To State Index
        create_sensor_to_state_index($device, $state_name, $sensor_index);
        // End CEC7 Commutator Alarm
    }

    // Motor Status
    $state_name = 'statusMotor';
    $states = [
        ['value' => 1, 'generic' => 2, 'graph' => 0, 'descr' => 'Running'],
        ['value' => 2, 'generic' => 0, 'graph' => 0, 'descr' => 'Stopped'],
        ['value' => 0, 'generic' => 3, 'graph' => 0, 'descr' => 'Unknown'],
    ];
    create_state_index($state_name, $states);

    $descr = 'Motor';
    $sensor_index = 0;
    // Discover Sensor
    discover_sensor(
        $valid['sensor'],
        'state',
        $device,
        null,
        $sensor_index,
        $state_name,
        $descr,
        1,
        1,
        null,
        null,
        null,
        null,
        $statusMotor,
        'snmp',
        null,
        null,
        null,
        'CEA7/CEM7'
    );
    // Create Sensor To State Index
    create_sensor_to_state_index($device, $state_name, $sensor_index);
    // End Motor Status

    // Control Unit mode
    $state_name = 'statusMode';
    $states = [
        ['value' => 4, 'generic' => 0, 'graph' => 0, 'descr' => 'Auto'],
        ['value' => 8, 'generic' => 2, 'graph' => 0, 'descr' => 'Manual'],
        ['value' => 16, 'generic' => 1, 'graph' => 0, 'descr' => 'Test'],
        ['value' => 32, 'generic' => 2, 'graph' => 0, 'descr' => 'Blocked'],
        ['value' => 0, 'generic' => 3, 'graph' => 0, 'descr' => 'Unknown'],
    ];
    create_state_index($state_name, $states);

    $descr = 'Mode';
    $sensor_index = 0;
    // Discover Sensors
    discover_sensor(
        $valid['sensor'],
        'state',
        $device,
        null,
        $sensor_index,
        $state_name,
        $descr,
        1,
        1,
        null,
        null,
        null,
        null,
        $statusMode,
        'snmp',
        null,
        null,
        null,
        'CEA7/CEM7'
    );

    // Create Sensor To State Index
    create_sensor_to_state_index($device, $state_name, $sensor_index);
    // End Control Unit mode

    // Transfer Pump Status
    $state_name = 'statusBT';
    $states = [
        ['value' => 0, 'generic' => 0, 'graph' => 0, 'descr' => 'Off'],
        ['value' => 64, 'generic' => 1, 'graph' => 0, 'descr' => 'On'],
    ];
    create_state_index($state_name, $states);

    $descr = 'Transfer Pump';
    $sensor_index = 0;
    //Discover Sensor
    discover_sensor(
        $valid['sensor'],
        'state',
        $device,
        null,
        $sensor_index,
        $state_name,
        $descr,
        1,
        1,
        null,
        null,
        null,
        null,
        $statusTransferPump,
        'snmp',
        null,
        null,
        null,
        'CEA7/CEM7'
    );

    //Create Sensor To State Index
    create_sensor_to_state_index($device, $state_name, $sensor_index);
    // End Transfer Pump

    // CEA7/CEM7 Alarm
    $state_name = 'statusAL';
    $states = [
        ['value' => 0, 'generic' => 0, 'graph' => 0, 'descr' => 'No Alarm'],
        ['value' => 128, 'generic' => 2, 'graph' => 0, 'descr' => 'Alarm'],
    ];
    create_state_index($state_name, $states);

    $descr = 'Alarm';
    $sensor_index = 0;
    //Discover Sensor
    discover_sensor(
        $valid['sensor'],
        'state',
        $device,
        null,
        $sensor_index,
        $state_name,
        $descr,
        1,
        1,
        null,
        null,
        null,
        null,
        $statusAlarm,
        'snmp',
        null,
        null,
        null,
        'CEA7/CEM7'
    );

    //Create Sensor To State Index
    create_sensor_to_state_index($device, $state_name, $sensor_index);
    // End CEA7/CEM7 Alarm

    // Commutator Mode
    $state_name = 'statusComm';
    if ($commgroup === 'CEC7') {
        $states = [
            ['value' => 32, 'generic' => 2, 'graph' => 0, 'descr' => 'Genset'],
            ['value' => 64, 'generic' => 0, 'graph' => 0, 'descr' => 'Mains'],
            ['value' => 0, 'generic' => 3, 'graph' => 0, 'descr' => 'Unknown'],
        ];
    } else {
        $states = [
            ['value' => 512, 'generic' => 2, 'graph' => 0, 'descr' => 'Genset'],
            ['value' => 256, 'generic' => 0, 'graph' => 0, 'descr' => 'Mains'],
            ['value' => 0, 'generic' => 3, 'graph' => 0, 'descr' => 'Unknown'],
        ];
    }

    create_state_index($state_name, $states);

    $descr = 'Commutator Mode';
    $sensor_index = 0;

    // Discover Sensors
    discover_sensor(
        $valid['sensor'],
        'state',
        $device,
        null,
        $sensor_index,
        $state_name,
        $descr,
        1,
        1,
        null,
        null,
        null,
        null,
        $statusComm,
        'snmp',
        null,
        null,
        null,
        $commgroup
    );

    // Create Sensor To State Index
    create_sensor_to_state_index($device, $state_name, $sensor_index);
    // End Commutator Mode
}
