<?php

$ds = 'time_squeeze';
$unit_text = 'Time Squeeze';
$rrd_filename = Rrd::name($device['hostname'], ['app', 'linux_softnet_stat', $app->app_id]);

require 'includes/html/graphs/generic_stats.inc.php';
