<?php

$scale_min = 0;

require 'includes/graphs/common.inc.php';

$drbd_rrd = rrd_name($device['hostname'], array('app', 'drbd', $app['app_instance']));

if (is_file($drbd_rrd)) {
    $rrd_filename = $drbd_rrd;
}

$ds_in  = 'nr';
$ds_out = 'ns';

$multiplier = '8';

require 'includes/graphs/generic_data.inc.php';
