<?php

/*
 *
 * OIDs obtained from First Network Group Inc. DHCPatriot operations manual version 6.4.x
 * Found here: http://www.network1.net/products/dhcpatriot/documentation/PDFs/v64xmanual-rev1.pdf
 *
*/

/* Set base OID value */
$pool_size_base_oid = '1.3.6.1.4.1.2021.50.130';

/* Set dhcp index values for authenticated and standard DHCP networks */
$auth_dhcp_index = '1';
$standard_dhcp_index = '2';

if ($sensor['sensor_type'] === 'dhcpatriotAuthDHCP') {
    $pool_size = snmp_get($device, $pool_size_base_oid . '.' . $auth_dhcp_index . '.' . $sensor['sensor_index'], '-Oqv');
}

if ($sensor['sensor_type'] === 'dhcpatriotStandardDHCP') {
    $pool_size = snmp_get($device, $pool_size_base_oid . '.' . $standard_dhcp_index . '.' . $sensor['sensor_index'], '-Oqv');
}

$prev_divisor = $sensor['sensor_divisor'];
$new_divisor = $pool_size;

$prev_descr = $sensor['sensor_descr'];
$descr_tmp = explode('(', $sensor['sensor_descr']);
$new_descr = $descr_tmp[0] . '(' . $sensor_value . '/' . $pool_size . ')';

if ($new_descr != $prev_descr) {
    $sensor['sensor_descr'] = $new_descr;
    $updated = dbUpdate(array('sensor_descr' => $new_descr), 'sensors', '`sensor_id` = ?', array($sensor['sensor_id']));
}

if ($new_divisor != $prev_divisor) {
    $sensor['sensor_divisor'] = $new_divisor;
    $updated = dbUpdate(array('sensor_divisor' => $new_divisor), 'sensors', '`sensor_id` = ?', array($sensor['sensor_id']));
    log_event('Sensor Divisor Updated: ' . $sensor['sensor_class'] . ' ' . $sensor['sensor_type'] . ' ' . $sensor['sensor_index'] . ' ' . $sensor['sensor_descr'] . ' old_divisor=' . $prev_divisor . ' new_divisor=' . $sensor['sensor_divisor'], $device, 'sensor', 3, $sensor['sensor_id']);
}

unset($pool_size_base_oid, $auth_dhcp_index, $standard_dhcp_index, $pool_size, $prev_divisor, $new_divisor, $descr_tmp, $prev_descr, $new_descr, $update, $updated);
