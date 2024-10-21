<?php

$rrd_filename = Rrd::name($device['hostname'], ['routeros-queuetree', $vars['rrd_id']]);

$colour_area = 'FF8888';
$colour_line = '880000';

$ds = 'dropbytes';

$multiplier = 8;

$unit_text = 'bps';

require 'includes/html/graphs/generic_simplex.inc.php';
