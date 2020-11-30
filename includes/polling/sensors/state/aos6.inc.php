<?php

if (($device['os'] == 'aos6') && ($sensor['sensor_type'] === 'alclnkaggAggNbrAttachedPorts')) {
    if (($sensor_value == 2) || ($sensor_value == 4) || ($sensor_value == 6) || ($sensor_value == 8)) {
        $sensor_value = 1;
    } else {
        $sensor_value = 2;
    }
}
