<?php

use LibreNMS\Util\Rewrite;

$rrd_list = $customer_ports->reduce(function (array $rrd, $port) {
    $rrd_filename = get_port_rrdfile_path($port->device->hostname, $port->port_id);
    if (Rrd::checkRrdExists($rrd_filename)) {
        $rrd[] = [
            'filename' => $rrd_filename,
            'descr' => $port->device->display . '-' . $port->ifDescr,
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
