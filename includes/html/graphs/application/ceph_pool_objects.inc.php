<?php

$scale_min = 0;

require 'includes/html/graphs/common.inc.php';

$rrd_filename = Rrd::name($device['hostname'], ['app', 'ceph', $app->app_id, 'df', $vars['pool']]);

$ds = 'objects';

$colour_area = 'EEEEEE';
$colour_line = '36393D';

$colour_area_max = 'FFEE99';

$unit_text = 'Objects';

require 'includes/html/graphs/generic_simplex.inc.php';
