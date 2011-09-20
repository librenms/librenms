<?php

$rrd_filename_in  = $config['rrd_dir'] . "/" . $device['hostname'] . "/ucd_ssIORawReceived.rrd";
$rrd_filename_out = $config['rrd_dir'] . "/" . $device['hostname'] . "/ucd_ssIORawSent.rrd";
$rra_in = "value";
$rra_out = "value";

$multiplier = 512;

include("includes/graphs/generic_bits.inc.php");

?>