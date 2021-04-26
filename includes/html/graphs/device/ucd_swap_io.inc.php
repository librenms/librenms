<?php

$rrd_filename_in = Rrd::name($device['hostname'], 'ucd_ssRawSwapIn');
$rrd_filename_out = Rrd::name($device['hostname'], 'ucd_ssRawSwapOut');
$ds_in = 'value';
$ds_out = 'value';

$multiplier = 512;

require 'includes/html/graphs/generic_data.inc.php';
