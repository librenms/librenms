<?php

$rrd_filename = Rrd::name($device['hostname'], 'venturi_transport_traffic');

require 'includes/html/graphs/common.inc.php';

$ds_in = 'TransportTrafficRx';
$ds_out = 'TransportTrafficRx';

$unit_text = 'Transport';

$colours = 'mixed';

$scale_min = '0';

#require 'includes/html/graphs/generic_multi_line.inc.php';
require 'includes/html/graphs/generic_data.inc.php';
