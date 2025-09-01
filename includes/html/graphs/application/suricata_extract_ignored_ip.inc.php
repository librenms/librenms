<?php

$ds = 'ignored_ip';
$unit_text = 'Ignored';
$descr = 'By IP';
$rrd_filename = Rrd::name($device['hostname'], ['app', 'suricata_extract', $app->app_id]);

require 'includes/html/graphs/generic_stats.inc.php';
