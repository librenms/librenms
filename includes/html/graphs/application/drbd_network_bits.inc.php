<?php

$scale_min = 0;

require 'includes/html/graphs/common.inc.php';

$rrd_filename = Rrd::name($device['hostname'], ['app', 'drbd', $app->app_instance]);

$ds_in = 'nr';
$ds_out = 'ns';

$multiplier = '8';

require 'includes/html/graphs/generic_data.inc.php';
