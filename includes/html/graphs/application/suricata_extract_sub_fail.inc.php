<?php

$ds = 'sub_fail';
$unit_text = 'Sub Fails';
$rrd_filename = Rrd::name($device['hostname'], ['app', 'suricata_extract', $app->app_id]);

require 'includes/html/graphs/generic_stats.inc.php';
