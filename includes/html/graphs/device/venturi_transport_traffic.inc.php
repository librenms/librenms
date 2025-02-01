<?php

$rrd_filename = Rrd::name($device['hostname'], 'venturi_transport_traffic');

require 'includes/html/graphs/common.inc.php';

$ds_in = 'TransportTrafficRx';
$ds_out = 'TransportTrafficRx';

$multiplier = 8;

require 'includes/html/graphs/generic_data.inc.php';
