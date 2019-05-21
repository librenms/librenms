<?php

require 'includes/html/graphs/common.inc.php';

$mysql_rrd = rrd_name($device['hostname'], array('app', 'mysql', $app['app_id']));

if (rrdtool_check_rrd_exists($mysql_rrd)) {
    $rrd_filename = $mysql_rrd;
}

$multiplier = 8;

$ds_in  = 'BRd';
$ds_out = 'BSt';

require 'includes/html/graphs/generic_data.inc.php';
