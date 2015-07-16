<?php

$rrd_filename = $config['rrd_dir'].'/'.$device['hostname'].'/cipsec_flow.rrd';

$ds_in  = 'InOctets';
$ds_out = 'OutOctets';

require 'includes/graphs/generic_data.inc.php';
