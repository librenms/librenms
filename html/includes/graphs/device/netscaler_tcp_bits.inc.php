<?php

$rrd_filename = rrd_name($device['hostname'], 'netscaler-stats-tcp');

$ds_in  = 'TotRxBytes';
$ds_out = 'TotTxBytes';

$multiplier = 8;

require 'includes/graphs/generic_data.inc.php';
