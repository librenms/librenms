<?php

$ds = 'cpu_collision';
$unit_text = 'CPU Collision';
$rrd_filename = Rrd::name($device['hostname'], ['app', 'linux_softnet_stat', $app->app_id]);

require 'includes/html/graphs/generic_stats.inc.php';
