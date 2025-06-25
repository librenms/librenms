<?php

$unit_text = 'nog_conns';
$descr = 'nog_conns';
$ds = 'nog_conns';

$rrd_filename = Rrd::name($device['hostname'], ['app', $app->app_type, $app->app_id]);

require 'includes/html/graphs/generic_stats.inc.php';
