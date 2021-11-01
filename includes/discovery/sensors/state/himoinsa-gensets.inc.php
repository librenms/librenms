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
 * @copyright  2021 Daniel Baeza
 * @author     TheGreatDoc <doctoruve@gmail.com>
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

// Check if CEC7 info is available and retrieve data
$statusConm = SnmpQuery::get('HIMOINSAv14-MIB::statusConm.0')->value();
// Check if CEA7/CEM7 info is available and retrieve data
$status = SnmpQuery::get('HIMOINSAv14-MIB::status.0')->value();

if (is_numeric($status)) {
    define('A', 0b0000000001);
    define('P', 0b0000000010);
    define('AU', 0b0000000100);
    define('MN', 0b0000001000);
    define('T', 0b0000010000);
    define('B', 0b0000100000);
    define('BT', 0b0001000000);
    define('AL', 0b0010000000);
    if (is_numeric($statusConm)) {
        define('SWAL', 0b0000001);
        define('G', 0b0100000);
        define('R', 0b1000000);
    } else {
        define('G', 0b0100000000);
        define('R', 0b1000000000);
    }
}

if (intval("0b".decbin($status), 2) & A) {
    $engine = 1;
} elseif (intval("0b".decbin($status), 2) & P) {
    $engine = 0;
} else {
    $engine = 9;
}
if (intval("0b".decbin($status), 2) & AU) {
    $mode = 1;
} elseif (intval("0b".decbin($status), 2) & MN) {
    $mode = 2;
} elseif (intval("0b".decbin($status), 2) & T) {
    $mode = 3;
} elseif (intval("0b".decbin($status), 2) & B) {
    $mode = 4;
} else {
    $mode = 9;
}
if (intval("0b".decbin($status), 2) & BT) {
    $tpump = 1;
} else {
    $tpump = 0;
}
if (intval("0b".decbin($status), 2) & AL) {
    $alarm = 1;
} else {
    $alarm = 0;
}
if (!is_numeric($statusConm)) {
    d_echo("No CEC7 installed");
    if (intval("0b".decbin($status), 2) & R) {
        $switch = 0;
    } elseif (intval("0b".decbin($status), 2) & G) {
        $switch = 1;
    } else {
        $switch = 9;
    }
} else {
    d_echo("CEC7 installed");
    $cec7 = true;
    if (intval("0b".decbin($statusConm), 2) & R) {
        $switch = 0;
    } elseif (intval("0b".decbin($statusConm), 2) & G) {
        $switch = 1;
    } else {
        $switch = 9;
    }
    if (intval("0b".decbin($statusConm), 2) & SWAL) {
        $switchalarm = 1;
    } else {
        $switchalarm = 0;
    }
}

d_echo("Engine: " . $engine);
d_echo("Mode: " . $mode);
d_echo("Switch: " . $switch);
d_echo("Alarm: " . $alarm);
d_echo("SWAlarm: " . $switchalarm);
d_echo("Transfer Pump: " . $tpump);

// Engine state
$state_name = "statusEngine";
$states = [
    ['value' => 1, 'generic' => 2, 'graph' => 0, 'descr' => 'Running'],
    ['value' => 0, 'generic' => 0, 'graph' => 0, 'descr' => 'Stopped'],
    ['value' => 9, 'generic' => 3, 'graph' => 0, 'descr' => 'Unknown'],
];
create_state_index($state_name, $states);

$descr = 'Engine';
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
    $engine,
    'snmp',
    null,
    null,
    null,
    'CEA7/CEM7'
);
//Create Sensor To State Index
create_sensor_to_state_index($device, $state_name, $sensor_index);

// CE Mode
$state_name = 'statusMode';
$states = [
    ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'Auto'],
    ['value' => 2, 'generic' => 2, 'graph' => 0, 'descr' => 'Manual'],
    ['value' => 3, 'generic' => 1, 'graph' => 0, 'descr' => 'Test'],
    ['value' => 4, 'generic' => 2, 'graph' => 0, 'descr' => 'Blocked'],
    ['value' => 9, 'generic' => 3, 'graph' => 0, 'descr' => 'Unknown'],
];
create_state_index($state_name, $states);

$descr = 'Mode';
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
    $mode,
    'snmp',
    null,
    null,
    null,
    'CEA7/CEM7'
);

//Create Sensor To State Index
create_sensor_to_state_index($device, $state_name, $sensor_index);

// Switch Mode
$state_name = 'statusSW';
$states = [
    ['value' => 1, 'generic' => 2, 'graph' => 0, 'descr' => 'Genset'],
    ['value' => 0, 'generic' => 0, 'graph' => 0, 'descr' => 'Mains'],
    ['value' => 9, 'generic' => 3, 'graph' => 0, 'descr' => 'Unknown'],
];
create_state_index($state_name, $states);

// Switch Mode
$group = $cec7 = true ? 'CEC7' : 'CEA7/CEM7';
$descr = 'Switch Mode';
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
    $switch,
    'snmp',
    null,
    null,
    null,
    $group
);

//Create Sensor To State Index
create_sensor_to_state_index($device, $state_name, $sensor_index);

// SW Alarm
if ($cec7) {
    $state_name = 'statusSWAL';
    $states = [
        ['value' => 0, 'generic' => 0, 'graph' => 0, 'descr' => 'No Alarm'],
        ['value' => 1, 'generic' => 2, 'graph' => 0, 'descr' => 'Alarm'],
    ];
    create_state_index($state_name, $states);

    $descr = 'SW Alarm';
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
        $switchalarm,
        'snmp',
        null,
        null,
        null,
        'CEC7'
    );

    //Create Sensor To State Index
    create_sensor_to_state_index($device, $state_name, $sensor_index);
}
// CEA7/CEM7 Alarm
$state_name = 'statusAL';
$states = [
    ['value' => 0, 'generic' => 0, 'graph' => 0, 'descr' => 'No Alarm'],
    ['value' => 1, 'generic' => 2, 'graph' => 0, 'descr' => 'Alarm'],
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
    $alarm,
    'snmp',
    null,
    null,
    null,
    'CEA7/CEM7'
);

//Create Sensor To State Index
create_sensor_to_state_index($device, $state_name, $sensor_index);

// Transfer Pump
$state_name = 'statusBT';
$states = [
    ['value' => 0, 'generic' => 0, 'graph' => 0, 'descr' => 'Off'],
    ['value' => 1, 'generic' => 1, 'graph' => 0, 'descr' => 'On'],
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
    $tpump,
    'snmp',
    null,
    null,
    null,
    'CEA7/CEM7'
);

//Create Sensor To State Index
create_sensor_to_state_index($device, $state_name, $sensor_index);
