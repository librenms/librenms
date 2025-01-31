<?php

$rrd_filename = Rrd::name($device['hostname'], 'venturi_capacity_bandwidth');

require 'includes/html/graphs/common.inc.php';

$ds = 'MaxTcpBandwidth';
$descr = 'TCP';

$unit_text = 'Capacity Bandwidth';

$total_units = 'Kbps';
$units = 'kb';

$colours = 'mixed';

$scale_min = '0';

require 'includes/html/graphs/generic_simplex.inc.php';