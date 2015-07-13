<?php

$rrd_filename = $config['rrd_dir'].'/'.$device['hostname'].'/netscaler-stats-tcp.rrd';

$ds_in  = 'TotRxBytes';
$ds_out = 'TotTxBytes';

$multiplier = 8;

require 'includes/graphs/generic_data.inc.php';
