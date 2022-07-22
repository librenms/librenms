<?php

$scale_min = 0;

require 'includes/html/graphs/common.inc.php';

$ceph_pool_rrd = Rrd::name($device['hostname'], ['app', 'ceph', $app->app_id, 'df', $vars['pool']]);

if (Rrd::checkRrdExists($ceph_pool_rrd)) {
    $rrd_filename = $ceph_pool_rrd;
}

$ds = 'objects';

$colour_area = 'EEEEEE';
$colour_line = '36393D';

$colour_area_max = 'FFEE99';

$unit_text = 'Objects';

require 'includes/html/graphs/generic_simplex.inc.php';
