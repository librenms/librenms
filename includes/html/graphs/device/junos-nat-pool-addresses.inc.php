<?php

/**
 * junos-nat-pool-addresses.inc.php
 *
 * Graphs address usage for a single Junos SRX NAT pool (withoutPAT/static
 * pools). Requires ?pool=<pool name> in the graph URL.
 */

$pool_name = $vars['pool'] ?? '';
if ($pool_name === '') {
    return;
}

$graph_title = 'NAT Pool Addresses - ' . $pool_name;

require 'includes/html/graphs/common.inc.php';

$graph_params->scale_min = 0;

$rrd_safe_name = preg_replace('/[^a-zA-Z0-9_-]/', '_', $pool_name);
$rrd_filename = Rrd::name($device['hostname'], ['junos', 'nat-pool', $rrd_safe_name]);

$rrd_options = [];

if (Rrd::checkRrdExists($rrd_filename)) {
    $rrd_options[] = "DEF:addr_inuse=$rrd_filename:addr_inuse:AVERAGE";
    $rrd_options[] = "DEF:addr_avail=$rrd_filename:addr_avail:AVERAGE";
    $rrd_options[] = 'AREA:addr_avail#cccccc:Addresses available';
    $rrd_options[] = 'AREA:addr_inuse#ff9900:Addresses in use';
    $rrd_options[] = 'LINE1.5:addr_inuse#cc6600';
    $rrd_options[] = 'GPRINT:addr_inuse:LAST:Cur\\:%6.0lf';
    $rrd_options[] = 'GPRINT:addr_inuse:MAX: Max\\:%6.0lf';
    $rrd_options[] = 'GPRINT:addr_inuse:AVERAGE: Avg\\:%6.0lf\\n';
    $rrd_options[] = 'GPRINT:addr_avail:LAST:Cur\\:%6.0lf';
    $rrd_options[] = 'GPRINT:addr_avail:MAX: Max\\:%6.0lf';
    $rrd_options[] = 'GPRINT:addr_avail:AVERAGE: Avg\\:%6.0lf\\n';
}
