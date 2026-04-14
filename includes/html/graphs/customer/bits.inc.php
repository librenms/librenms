<?php

use App\Facades\LibrenmsConfig;
use App\Models\Port;
use LibreNMS\Util\Rewrite;

// Generate a list of ports and then call the multi_bits grapher to generate from the list

$ports = Port::with('device')
    ->where('port_descr_descr', $vars['id'])
    ->whereIn('port_descr_type', LibrenmsConfig::get('customers_descr', ['cust']))
    ->get();
$rrd_list = [];
foreach ($ports as $port) {
    $rrd_filename = get_port_rrdfile_path($port->hostname, $port->port_id); // FIXME: Unification OK?
    if (Rrd::checkRrdExists($rrd_filename)) {
        $rrd_list[] = [
            'filename' => $rrd_filename,
            'descr' => $port->hostname . '-' . $port->ifDescr,
            'descr_in' => $port->device->shortDisplayName(),
            'descr_out' => Rewrite::shortenIfName($port->ifDescr),
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
