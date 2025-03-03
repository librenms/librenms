<?php

/*
 *
 * OIDs obtained from First Network Group Inc. DHCPatriot operations manual version 6.4.x
 * Found here: http://www.network1.net/products/dhcpatriot/documentation/PDFs/v64xmanual-rev1.pdf
 *
*/

if ($sensor['sensor_type'] === 'dhcpatriotLicenseExpiration') {
    $current_time = time();
    $epoch_time = explode(':', $sensor_value);
    $sensor_value = round((intval($epoch_time[1]) - $current_time) / (60 * 60 * 24));
}
