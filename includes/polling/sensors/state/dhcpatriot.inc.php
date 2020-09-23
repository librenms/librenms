<?php

/*
 *
 * OIDs obtained from First Network Group Inc. DHCPatriot operations manual version 6.4.x
 * Found here: http://www.network1.net/products/dhcpatriot/documentation/PDFs/v64xmanual-rev1.pdf
 *
*/

if ($sensor['sensor_type'] === 'dhcpatriotServiceStatus') {
    $current_time = time();
    $sensor_value_tmp = explode(':', $sensor_value);
    $sensor_value = intval($sensor_value_tmp[1]);

    if (abs(intval($sensor_value_tmp[0]) - $current_time) > 300) {
        $sensor_value = 2;
    }
    if ($sensor_value_tmp[1] === '999') {
        $sensor_value = 3;
    }
}

unset($current_time, $sensor_value_tmp);
