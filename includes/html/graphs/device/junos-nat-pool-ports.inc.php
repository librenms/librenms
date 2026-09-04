<?php

/**
 * junos-nat-pool-ports.inc.php
 *
 * Graphs port usage for a single Junos SRX NAT pool (withPAT pools).
 * Requires ?pool=<pool name> in the graph URL.
 */

$pool_name = $vars['pool'] ?? '';
if ($pool_name === '') {
    return;
}

$graph_title = 'NAT Pool Ports - ' . $pool_name;

require 'includes/html/graphs/common.inc.php';

$graph_params->scale_min = 0;

$rrd_safe_name = preg_replace('/[^a-zA-Z0-9_-]/', '_', $pool_name);
$rrd_filename = Rrd::name($device['hostname'], ['junos', 'nat-pool', $rrd_safe_name]);

$rrd_options = [];

if (Rrd::checkRrdExists($rrd_filename)) {
    $rrd_options[] = "DEF:ports_inuse=$rrd_filename:ports_inuse:AVERAGE";
    $rrd_options[] = "DEF:ports_avail=$rrd_filename:ports_avail:AVERAGE";
    $rrd_options[] = 'AREA:ports_avail#cccccc:Ports available';
    $rrd_options[] = 'AREA:ports_inuse#00b5ff:Ports in use';
    $rrd_options[] = 'LINE1.5:ports_inuse#0080cc';
    $rrd_options[] = 'GPRINT:ports_inuse:LAST:Cur\\:%8.0lf';
    $rrd_options[] = 'GPRINT:ports_inuse:MAX: Max\\:%8.0lf';
    $rrd_options[] = 'GPRINT:ports_inuse:AVERAGE: Avg\\:%8.0lf\\n';
    $rrd_options[] = 'GPRINT:ports_avail:LAST:Cur\\:%8.0lf';
    $rrd_options[] = 'GPRINT:ports_avail:MAX: Max\\:%8.0lf';
    $rrd_options[] = 'GPRINT:ports_avail:AVERAGE: Avg\\:%8.0lf\\n';
}
