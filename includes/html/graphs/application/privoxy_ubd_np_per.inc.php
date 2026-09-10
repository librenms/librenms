<?php

$unit_text = 'ubd_np_per';
$descr = 'ubd_np_per';
$ds = 'ubd_np_per';

$rrd_filename = Rrd::name($device['hostname'], ['app', $app->app_type, $app->app_id]);

require 'includes/html/graphs/generic_stats.inc.php';
