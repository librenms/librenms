<?php

$ds = 'zero_sized';
$unit_text = 'Files';
$descr = 'Zero Sized';
$rrd_filename = Rrd::name($device['hostname'], ['app', 'suricata_extract', $app->app_id]);

require 'includes/html/graphs/generic_stats.inc.php';
