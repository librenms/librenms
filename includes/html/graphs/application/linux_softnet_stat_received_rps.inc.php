<?php

$ds = 'received_rps';
$unit_text = 'RPS/Sec';
$rrd_filename = Rrd::name($device['hostname'], ['app', 'linux_softnet_stat', $app->app_id]);

require 'includes/html/graphs/generic_stats.inc.php';
