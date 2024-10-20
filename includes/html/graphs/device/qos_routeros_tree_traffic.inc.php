<?php

$rrd_filename = Rrd::name($device['hostname'], ['routeros-queuetree', $vars['rrd_id']]);

$colour_area = '9999cc';
$colour_line = '0000cc';

$ds = 'sentbytes';

$multiplier = 8;

$unit_text = 'bps';

require 'includes/html/graphs/generic_simplex.inc.php';
