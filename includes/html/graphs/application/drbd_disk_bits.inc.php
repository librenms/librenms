<?php

$scale_min = 0;

require 'includes/html/graphs/common.inc.php';

$drbd_rrd = Rrd::name($device['hostname'], ['app', 'drbd', $app['app_instance']]);

if (Rrd::checkRrdExists($drbd_rrd)) {
    $rrd_filename = $drbd_rrd;
}

$ds_in = 'dr';
$ds_out = 'dw';

$multiplier = '8';
$format = 'bytes';

require 'includes/html/graphs/generic_data.inc.php';
