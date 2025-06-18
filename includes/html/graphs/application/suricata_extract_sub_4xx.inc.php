<?php

$ds = 'sub_4xx';
$unit_text = 'HTML Status';
$descr = '4xx';
$rrd_filename = Rrd::name($device['hostname'], ['app', 'suricata_extract', $app->app_id]);

require 'includes/html/graphs/generic_stats.inc.php';
