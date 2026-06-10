<?php

$ds = 'ignored_host';
$unit_text = 'Ignored';
$descr = 'By Host';
$rrd_filename = Rrd::name($device['hostname'], ['app', 'suricata_extract', $app->app_id]);

require 'includes/html/graphs/generic_stats.inc.php';
