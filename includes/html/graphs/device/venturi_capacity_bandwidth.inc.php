<?php

$rrd_filename = Rrd::name($device['hostname'], 'venturi_capacity_bandwidth');

require 'includes/html/graphs/common.inc.php';

$in = 'MaxTcpBandwidth';

require 'includes/html/graphs/generic_data.inc.php';
