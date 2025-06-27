<?php

$ds = 'truncated';
$unit_text = 'Files';
$descr = 'Truncated';
$rrd_filename = Rrd::name($device['hostname'], ['app', 'suricata_extract', $app->app_id]);

require 'includes/html/graphs/generic_stats.inc.php';
