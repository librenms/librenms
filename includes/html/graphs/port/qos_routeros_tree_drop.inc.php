<?php

$qos = \App\Models\Qos::find($vars['qos_id']);
$rrd_filename = Rrd::name($device['hostname'], ['routeros-queuetree', $qos->rrd_id]);

$colour_area = 'FF8888';
$colour_line = '880000';

$ds = 'droppackets';

$unit_text = 'packets/s';

require 'includes/html/graphs/generic_simplex.inc.php';
