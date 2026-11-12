<?php

$rrd_filename = Rrd::metricName($vminfo->metricIdentity('vm.diskops'));

if (! Rrd::checkRrdExists($rrd_filename)) {
    throw new \LibreNMS\Exceptions\RrdGraphException('No Data');
}

$colour_area_in = 'CAE853';
$colour_line_in = '75BA30';
$colour_area_out = 'A9A9F2';
$colour_line_out = '6A6AB7';

$ds_in = 'read_ops';
$ds_out = 'write_ops';
$unit_text = 'Ops/sec';

require 'includes/html/graphs/generic_duplex.inc.php';
