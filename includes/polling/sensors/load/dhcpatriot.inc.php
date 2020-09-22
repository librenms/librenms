<?php

/*
 *
 * OIDs obtained from First Network Group Inc. DHCPatriot operations manual version 6.4.x
 * Found here: http://www.network1.net/products/dhcpatriot/documentation/PDFs/v64xmanual-rev1.pdf
 *
*/

$prev_divisor = $sensor['sensor_divisor'];
$new_divisor = snmp_get($device, str_replace('.1.3.6.1.4.1.2021.50.120', '.1.3.6.1.4.1.2021.50.130', $sensor['sensor_oid']), '-Oqv');

$prev_descr = $sensor['sensor_descr'];
$new_descr = explode('(', $sensor['sensor_descr'])[0] . '(' . $sensor_value . '/' . $new_divisor . ')';

if ($new_divisor != $prev_divisor) {
    $sensor['sensor_divisor'] = $new_divisor;
    dbUpdate(['sensor_divisor' => $new_divisor], 'sensors', '`sensor_id` = ?', [$sensor['sensor_id']]);
    log_event('Sensor Divisor Updated: ' . $sensor['sensor_class'] . ' ' . $sensor['sensor_type'] . ' ' . $sensor['sensor_index'] . ' ' . $sensor['sensor_descr'] . ' old_divisor=' . $prev_divisor . ' new_divisor=' . $sensor['sensor_divisor'], $device, 'sensor', 3, $sensor['sensor_id']);
}

if ($new_descr != $prev_descr) {
    $sensor['sensor_descr'] = $new_descr;
    dbUpdate(['sensor_descr' => $new_descr], 'sensors', '`sensor_id` = ?', [$sensor['sensor_id']]);
}

unset($prev_divisor, $new_divisor, $prev_descr, $new_descr);
