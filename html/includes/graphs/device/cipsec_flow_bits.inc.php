<?php

$rrd_filename = rrd_name($device['hostname'], 'cipsec_flow');

$ds_in  = 'InOctets';
$ds_out = 'OutOctets';

require 'includes/graphs/generic_data.inc.php';
