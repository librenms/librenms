<?php

$unit_text = 'req_put';
$descr = 'req_put';
$ds = 'req_put';

$rrd_filename = Rrd::name($device['hostname'], ['app', $app->app_type, $app->app_id]);

require 'includes/html/graphs/generic_stats.inc.php';
