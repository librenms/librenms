<?php


$rrd_filename_in  = $config['rrd_dir'] . "/" . $device['hostname'] . "/ucd_ssRawSwapIn.rrd";
$rrd_filename_out = $config['rrd_dir'] . "/" . $device['hostname'] . "/ucd_ssRawSwapOut.rrd"; 
$rra_in = "value";
$rra_out = "value";

include("includes/graphs/generic_bits.inc.php");


?>
