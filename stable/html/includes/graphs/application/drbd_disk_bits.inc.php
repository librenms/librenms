<?php

$scale_min = 0;

include("includes/graphs/common.inc.php");

$drbd_rrd   = $config['rrd_dir'] . "/" . $device['hostname'] . "/app-drbd-".$app['app_instance'].".rrd";

if (is_file($drbd_rrd))
{
  $rrd_filename = $drbd_rrd;
}

$ds_in = "dr";
$ds_out = "dw";

$multiplier = "8";

include("includes/graphs/generic_bits.inc.php");

?>
