<?php

$rrd_filename = Rrd::name($device['hostname'], 'cipsec_flow');

$ds_in = 'InOctets';
$ds_out = 'OutOctets';

require 'includes/html/graphs/generic_data.inc.php';
