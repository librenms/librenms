<?php

$ds = 'frequency';
$unit_text = 'Frequency [ppm]';
$rrd_filename = Rrd::name($device['hostname'], ['app', 'systemd-timesyncd', $app->app_id]);

require 'includes/html/graphs/generic_stats.inc.php';
