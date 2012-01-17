<?php

$rrd_filename = $config['rrd_dir'] . "/" . $device['hostname'] . "/netscaler-stats-tcp.rrd";

$ds_in = "TotRxBytes";
$ds_out = "TotTxBytes";

include("includes/graphs/generic_bits.inc.php");


?>
