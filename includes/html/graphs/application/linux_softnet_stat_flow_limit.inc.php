<?php

$ds = 'flow_limit';
$unit_text = 'Limit Hits/Sec';
$filename = Rrd::name($device['hostname'], ['app', 'linux_softnet_stat', $app->app_id]);


require 'includes/html/graphs/generic_stats.inc.php';
