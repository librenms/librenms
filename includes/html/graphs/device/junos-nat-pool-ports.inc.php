<?php

/**
 * junos-nat-pool-ports.inc.php
 *
 * Graphs port usage for a single Junos SRX NAT pool (withPAT pools).
 * Requires ?pool=<pool name>&addr_type=<raw addr_type index value> in the
 * graph URL -- addr_type must match the raw value the poller saw for this
 * pool (see junos-nat-pool.inc.php), since a pool name is only unique
 * within one address family, not across the whole device.
 */

$pool_name = $vars['pool'] ?? '';
$addr_type = $vars['addr_type'] ?? '';
if ($pool_name === '' || $addr_type === '') {
    return;
}

$graph_title = 'NAT Pool Ports - ' . $pool_name;

require 'includes/html/graphs/common.inc.php';

$graph_params->scale_min = 0;

$rrd_safe_name = preg_replace('/[^a-zA-Z0-9_-]/', '_', $pool_name);
$rrd_filename = Rrd::name($device['hostname'], ['junos', 'nat-pool', $rrd_safe_name, (string) $addr_type]);

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
