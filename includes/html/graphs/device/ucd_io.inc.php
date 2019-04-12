<?php

$rrd_filename_in  = rrd_name($device['hostname'], 'ucd_ssIORawReceived');
$rrd_filename_out = rrd_name($device['hostname'], 'ucd_ssIORawSent');
$ds_in            = 'value';
$ds_out           = 'value';

$multiplier = 512;

require 'includes/html/graphs/generic_data.inc.php';
