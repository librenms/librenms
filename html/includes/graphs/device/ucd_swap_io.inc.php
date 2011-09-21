<?php

$rrd_filename_in  = $config['rrd_dir'] . "/" . $device['hostname'] . "/ucd_ssRawSwapIn.rrd";
$rrd_filename_out = $config['rrd_dir'] . "/" . $device['hostname'] . "/ucd_ssRawSwapOut.rrd";
$ds_in = "value";
$ds_out = "value";

$multiplier = 512;

include("includes/graphs/generic_bits.inc.php");

?>