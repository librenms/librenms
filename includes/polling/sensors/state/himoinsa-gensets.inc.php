<?php

if ($device['os'] == 'himoinsa-gensets') {
    d_echo("Polling Himoinsa Gensets State Sensor \n");
    if (in_array($sensor['sensor_type'], ['statusAL', 'statusMode', 'statusSW', 'statusSWAL', 'statusEngine', 'statusBT'])) {
        switch ($sensor['sensor_type']) {
            case "statusBT":
                // Check if CEA7/CEM7 info is available and retrieve data
                $status = SnmpQuery::get('HIMOINSAv14-MIB::status.0')->value();
                define('BT', 0b0001000000);
                if (intval("0b".decbin($status), 2) & BT) {
                    $tpump = 1;
                } else {
                    $tpump = 0;
                }
                d_echo("Transfer Pump: " . $tpump);
                $sensor_value = $tpump;
                break;
            case "statusAL":
                // Check if CEA7/CEM7 info is available and retrieve data
                $status = SnmpQuery::get('HIMOINSAv14-MIB::status.0')->value();
                define('AL', 0b0010000000);
                if (intval("0b".decbin($status), 2) & AL) {
                    $alarm = 1;
                } else {
                    $alarm = 0;
                }
                d_echo("Alarm: " . $alarm);
                $sensor_value = $alarm;
                break;
            case "statusMode":
                // Check if CEA7/CEM7 info is available and retrieve data
                $status = SnmpQuery::get('HIMOINSAv14-MIB::status.0')->value();
                define('AU', 0b0000000100);
                define('MN', 0b0000001000);
                define('T', 0b0000010000);
                define('B', 0b0000100000);
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
                d_echo("Mode: " . $mode);
                $sensor_value = $mode;
                break;
            case "statusSW":
                // Check if CEC7 info is available and retrieve data
                $statusConm = SnmpQuery::get('HIMOINSAv14-MIB::statusConm.0')->value();
                // Check if CEA7/CEM7 info is available and retrieve data
                $status = SnmpQuery::get('HIMOINSAv14-MIB::status.0')->value();
                if (is_numeric($statusConm)) {
                    define('G', 0b0100000);
                    define('R', 0b1000000);
                } else {
                    define('G', 0b0100000000);
                    define('R', 0b1000000000);
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
                    if (intval("0b".decbin($statusConm), 2) & R) {
                        $switch = 0;
                    } elseif (intval("0b".decbin($statusConm), 2) & G) {
                        $switch = 1;
                    } else {
                        $switch = 9;
                    }
                }
                d_echo("Switch: " . $switch);
                $sensor_value = $switch;
                break;
            case "statusSWAL":
                // Check if CEC7 info is available and retrieve data
                $statusConm = SnmpQuery::get('HIMOINSAv14-MIB::statusConm.0')->value();
                if (is_numeric($statusConm)) {
                    define('SWAL', 0b0000001);
                    if (intval("0b".decbin($statusConm), 2) & SWAL) {
                        $switchalarm = 1;
                    } else {
                        $switchalarm = 0;
                    }
                }
                d_echo("SWAlarm: " . $switchalarm);
                $sensor_value = $switchalarm;
                break;
            case "statusEngine":
                // Check if CEA7/CEM7 info is available and retrieve data
                $status = SnmpQuery::get('HIMOINSAv14-MIB::status.0')->value();
                define('A', 0b0000000001);
                define('P', 0b0000000010);
                if (intval("0b".decbin($status), 2) & A) {
                    $engine = 1;
                } elseif (intval("0b".decbin($status), 2) & P) {
                    $engine = 0;
                } else {
                    $engine = 9;
                }
                d_echo("Engine: " . $engine);
                $sensor_value = $engine;
                break;
        }
    }
}
