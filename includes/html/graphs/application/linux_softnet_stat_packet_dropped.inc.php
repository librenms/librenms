<?php

$ds = 'packet_dropped';
$unit_text = 'Pkt Drops/Sec';
$rrd_filename = Rrd::name($device['hostname'], ['app', 'linux_softnet_stat', $app->app_id]);

require 'includes/html/graphs/generic_stats.inc.php';
