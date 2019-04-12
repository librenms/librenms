<?php

$rrd_filename = rrd_name($device['hostname'], 'cipsec_flow');

$ds_in  = 'InOctets';
$ds_out = 'OutOctets';

require 'includes/html/graphs/generic_data.inc.php';
