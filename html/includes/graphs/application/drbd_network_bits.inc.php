<?php

$scale_min = 0;

require 'includes/html/graphs/common.inc.php';

$drbd_rrd = rrd_name($device['hostname'], array('app', 'drbd', $app['app_instance']));

if (rrdtool_check_rrd_exists($drbd_rrd)) {
    $rrd_filename = $drbd_rrd;
}

$ds_in  = 'nr';
$ds_out = 'ns';

$multiplier = '8';

require 'includes/html/graphs/generic_data.inc.php';
