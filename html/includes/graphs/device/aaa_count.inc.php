<?php

$scale_min = '0';

require 'includes/graphs/common.inc.php';

$rrd_filename = rrd_name($device['hostname'], array('aaa','count'));
if (rrdtool_check_rrd_exists($rrd_filename)) {
    $rrd_filename = $rrd_filename;
}else {
    echo "file missing: $rrd_filename";
}

$colour_area = 'FFB74D';
$colour_line = 'E65100';

$unit_text = 'AAA';
$line_text = 'Online';

$ds = 'online';

require 'includes/graphs/generic_simplex.inc.php';



