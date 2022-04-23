<?php

use LibreNMS\Config;

// Generate a list of ports and then call the multi_bits grapher to generate from the list

$cust_descrs = (array) Config::get('customers_descr', ['cust']);

$sql = 'SELECT * FROM `ports` AS I, `devices` AS D WHERE `port_descr_descr` = ? AND D.device_id = I.device_id AND `port_descr_type` IN ' . dbGenPlaceholders(count($cust_descrs));
$param = $cust_descrs;
array_unshift($param, $vars['id']);

$rrd_list = [];
foreach (dbFetchRows($sql, $param) as $port) {
    $rrd_filename = get_port_rrdfile_path($port['hostname'], $port['port_id']); // FIXME: Unification OK?
    if (Rrd::checkRrdExists($rrd_filename)) {
        $rrd_list[] = [
            'filename'  => $rrd_filename,
            'descr'     => $port['hostname'] . '-' . $port['ifDescr'],
            'descr_in'  => shorthost($port['hostname']),
            'descr_out' => makeshortif($port['ifDescr']),
        ];
    }
}

$units = 'bps';
$total_units = 'B';
$colours_in = 'greens';
$multiplier = '8';
$colours_out = 'blues';

$nototal = 1;

$ds_in = 'INOCTETS';
$ds_out = 'OUTOCTETS';

require 'includes/html/graphs/generic_multi_bits_separated.inc.php';
