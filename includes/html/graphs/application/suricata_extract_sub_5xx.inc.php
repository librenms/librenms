<?php

$ds = 'sub_5xx';
$unit_text = 'HTML Status';
$descr = '5xx';
$rrd_filename = Rrd::name($device['hostname'], ['app', 'suricata_extract', $app->app_id]);

require 'includes/html/graphs/generic_stats.inc.php';
