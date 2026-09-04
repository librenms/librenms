<?php

/**
 * junos-nat-pool-addresses.inc.php
 *
 * Graphs address usage for a single Junos SRX NAT pool (withoutPAT/static
 * pools). Requires ?pool=<pool name>&addr_type=<raw addr_type index value>
 * in the graph URL -- addr_type must match the raw value the poller saw
 * for this pool (see junos-nat-pool.inc.php), since a pool name is only
 * unique within one address family, not across the whole device.
 */

$pool_name = $vars['pool'] ?? '';
$addr_type = $vars['addr_type'] ?? '';
if ($pool_name === '' || $addr_type === '') {
    return;
}

$graph_title = 'NAT Pool Addresses - ' . $pool_name;

require 'includes/html/graphs/common.inc.php';

$graph_params->scale_min = 0;

$rrd_safe_name = preg_replace('/[^a-zA-Z0-9_-]/', '_', $pool_name);
$rrd_filename = Rrd::name($device['hostname'], ['junos', 'nat-pool', $rrd_safe_name, (string) $addr_type]);

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
