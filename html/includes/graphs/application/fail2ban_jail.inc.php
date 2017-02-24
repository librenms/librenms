<?php
require 'includes/graphs/common.inc.php';

$rrd = rrd_name($device['hostname'], array('app',  $app['app_type'], $app['app_id'], $vars['jail']));
if (rrdtool_check_rrd_exists($rrd)) {
$rrd_filename = $rrd;
}

$ds  = 'banned';
$colour_area = 'f2eef4';
$colour_line = '582A72';
$unit_text = 'Banned IPs';

require 'includes/graphs/generic_simplex.inc.php';
