<?php

$ds = 'offset';
$unit_text = 'Offset';
$rrd_filename = Rrd::name($device['hostname'], ['app', 'ntp-server', $app->app_id]);

require 'includes/html/graphs/generic_stats.inc.php';
