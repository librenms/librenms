<?php

use App\Models\Port;
use LibreNMS\Config;

// Generate a list of ports and then call the multi_bits grapher to generate from the list

$cust_descrs = (array) Config::get('peering_descr', ['peering']);
$ports = Port::with('device')->where('port_descr_descr', $vars['id'])->whereIn('port_descr_type', $cust_descrs);

$rrd_list = [];
foreach ($ports as $port) {
    $rrd_filename = get_port_rrdfile_path($port['device']['hostname'], $port['port_id']); // FIXME: Unification OK?
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
