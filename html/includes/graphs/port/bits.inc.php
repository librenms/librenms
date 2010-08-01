<?php

$rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/" . safename($port['ifIndex'] . ".rrd");

$rra_in = "INOCTETS";
$rra_out = "OUTOCTETS";

include("includes/graphs/generic_bits.inc.php");

?>
