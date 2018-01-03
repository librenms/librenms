<?php

$rrd_filename = rrd_name($device['hostname'], 'vibe_global_rtp');

require 'includes/graphs/common.inc.php';

$ds = 'global_rtp';

$colour_area = '9999cc';
$colour_line = '0000cc';

$colour_area_max = '9999cc';

$graph_max = 1;

$unit_text = 'Global RTP Sessions';

require 'includes/graphs/generic_simplex.inc.php';
