<?php

$scale_min = 0;

require 'includes/html/graphs/common.inc.php';

$ceph_pool_rrd = ceph_rrd('pool');

if (Rrd::checkRrdExists($ceph_pool_rrd)) {
    $rrd_filename = $ceph_pool_rrd;
}

$ds = 'ops';

$colour_area = 'EEEEEE';
$colour_line = '36393D';

$colour_area_max = 'FFEE99';

$graph_max = 1;

$unit_text = 'Operations';

require 'includes/html/graphs/generic_simplex.inc.php';
