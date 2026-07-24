<?php

$ds = 'frequency';
$unit_text = 'Frequency';
$rrd_filename = Rrd::name($device['hostname'], ['app', 'ntp', $app->app_id]);

require 'includes/html/graphs/generic_stats.inc.php';
