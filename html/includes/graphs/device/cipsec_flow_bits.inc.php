<?php

$rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/cipsec_flow.rrd";

$rra_in = "InOctets";
$rra_out = "OutOctets";

include("includes/graphs/generic_bits.inc.php");

?>