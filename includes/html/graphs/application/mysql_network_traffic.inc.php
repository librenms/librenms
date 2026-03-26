<?php

require 'includes/html/graphs/common.inc.php';

$rrd_filename = Rrd::name($device['hostname'], ['app', 'mysql', $app->app_id]);

$multiplier = 8;

$ds_in = 'BRd';
$ds_out = 'BSt';

require 'includes/html/graphs/generic_data.inc.php';
