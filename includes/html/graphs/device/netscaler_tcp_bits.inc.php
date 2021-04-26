<?php

$rrd_filename = Rrd::name($device['hostname'], 'netscaler-stats-tcp');

$ds_in = 'TotRxBytes';
$ds_out = 'TotTxBytes';

$multiplier = 8;

require 'includes/html/graphs/generic_data.inc.php';
