<?php

$qos = \App\Models\Qos::find($vars['qos_id']);
$rrd_filename = Rrd::name($device['hostname'], ['routeros-queuetree', $qos->rrd_id]);

$colour_area = '90b040';
$colour_line = '7A9C35';

$ds = 'bytes';

$multiplier = 8;

$unit_text = 'bps';

require 'includes/html/graphs/generic_simplex.inc.php';
