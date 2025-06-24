<?php

$ds = 'ignored_ip_dest';
$unit_text = 'Ignored';
$descr = 'By IP Dest';
$rrd_filename = Rrd::name($device['hostname'], ['app', 'suricata_extract', $app->app_id]);

require 'includes/html/graphs/generic_stats.inc.php';
