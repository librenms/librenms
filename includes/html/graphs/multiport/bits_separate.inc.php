<?php

use LibreNMS\Util\Rewrite;

$i = 0;
$port_ids = array_values(array_filter(array_map(intval(...), explode(',', (string) $vars['id']))));
$port_models = \App\Models\Port::query()->with('device')->whereIn('port_id', $port_ids)->get()->keyBy('port_id');

foreach ($port_ids as $ifid) {
    $port = $port_models[$ifid] ?? null;
    if (! $port?->device) {
        continue;
    }

    $rrd_file = get_port_rrdfile_path($port->device->hostname, $ifid);
    if (Rrd::checkRrdExists($rrd_file)) {
        $device = $port->device->only(['hostname', 'sysName', 'display']);
        $rrd_list[$i]['filename'] = $rrd_file;
        $rrd_list[$i]['descr'] = format_hostname($device) . ' ' . $port['ifDescr'];
        $rrd_list[$i]['descr_in'] = format_hostname($device);
        $rrd_list[$i]['descr_out'] = Rewrite::shortenIfName($port->getLabel());
        $i++;
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
