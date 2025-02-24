<?php

$rrd_filename = Rrd::name($device['hostname'], 'venturi_transport_compressor_traffic');

require 'includes/html/graphs/common.inc.php';

$ds_in = 'TrafficFromCompressor';
$ds_out = 'TrafficToCompressor';

$multiplier = 8;

require 'includes/html/graphs/generic_data.inc.php';
