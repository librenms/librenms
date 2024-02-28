<?php

/**
 * For ZTT MSJ devices
 *
 * Rectifier module alarm - Rectifiermodule
 * Note: ZTT MSJ device state 0 = faulty, 1 = normal
 * So we manually change the state to fit the system when polling
 */
if ($sensor['sensor_oid'] == '.1.3.6.1.4.1.49692.1.4.1.1.49.1') {
    if ($sensor_value == 1) {
        $sensor_value = 0;
    } else {
        $sensor_value = 1;
    }
}

if ($sensor['sensor_oid'] == '.1.3.6.1.4.1.49692.1.4.1.1.50.1') {
    if ($sensor_value == 1) {
        $sensor_value = 0;
    } else {
        $sensor_value = 1;
    }
}

if ($sensor['sensor_oid'] == '.1.3.6.1.4.1.49692.1.4.1.1.51.1') {
    if ($sensor_value == 1) {
        $sensor_value = 0;
    } else {
        $sensor_value = 1;
    }
}

if ($sensor['sensor_oid'] == '.1.3.6.1.4.1.49692.1.4.1.1.52.1') {
    if ($sensor_value == 1) {
        $sensor_value = 0;
    } else {
        $sensor_value = 1;
    }
}
