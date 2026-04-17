<?php

use App\Facades\LibrenmsConfig;
use App\Models\Port;
use LibreNMS\Util\Rewrite;

// Generate a list of ports and then call the multi_bits grapher to generate from the list

$rrd_list = Port::with('device')
    ->where('port_descr_descr', $vars['id'])
    ->whereIn('port_descr_type', LibrenmsConfig::get('customers_descr', ['cust']))
    ->get()
    ->reduce(function (array $rrd, $port) {
        $rrd_filename = get_port_rrdfile_path($port->hostname, $port->port_id);
        if (Rrd::checkRrdExists($rrd_filename)) {
            $rrd[] = [
                'filename' => $rrd_filename,
                'descr' => $port->device->hostname . '-' . $port->ifDescr,
                'descr_in' => $port->device->shortDisplayName(),
                'descr_out' => Rewrite::shortenIfName($port->ifDescr),
            ];
        }

        return $rrd;
    }, []);

$units = 'bps';
$total_units = 'B';
$colours_in = 'greens';
$multiplier = '8';
$colours_out = 'blues';

$nototal = 1;

$ds_in = 'INOCTETS';
$ds_out = 'OUTOCTETS';

require 'includes/html/graphs/generic_multi_bits_separated.inc.php';
