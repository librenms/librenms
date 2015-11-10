<?php

$scale_min = 0;

require 'includes/graphs/common.inc.php';

$rrd = join('-', array('app', 'ceph', $vars['id'], 'df', $vars['pool'])).'.rrd';

$ceph_pool_rrd = join('/', array($config['rrd_dir'], $device['hostname'], $rrd));

if (is_file($ceph_pool_rrd)) {
    $rrd_filename = $ceph_pool_rrd;
}

$ds = 'objects';

$colour_area = 'EEEEEE';
$colour_line = '36393D';

$colour_area_max = 'FFEE99';

$unit_text = 'Objects';

require 'includes/graphs/generic_simplex.inc.php';
